<?php

declare(strict_types=1);

namespace duels\arena;

use duels\arena\task\GameCountDownUpdateTask;
use duels\arena\task\GameMatchUpdateTask;
use duels\asyncio\FileCopyAsyncTask;
use duels\Duels;
use duels\event\arena\ArenaStartEvent;
use duels\event\session\SessionJoinArenaEvent;
use duels\session\Session;
use duels\task\TaskHandlerStorage;
use duels\translation\Translation;
use duels\utils\Scoreboard;
use duels\arena\task\GameFinishUpdateTask;
use pocketmine\level\Level as pocketLevel;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class Arena extends TaskHandlerStorage {

    /** @var int */
    public const STATUS_WAITING = 1;
    public const STATUS_IN_GAME = 4;
    public const STATUS_FINISHING = 5;

    /** @var bool */
    private $isPremium;
    /** @var Level */
    protected $level;
    /** @var string */
    protected $worldName;
    /** @var array<string, Session> */
    protected $sessions = [];
    /** @var array<string, Session> */
    protected $spectators = [];
    /** @var int */
    protected $status = self::STATUS_WAITING;
    /** @var Scoreboard */
    protected $scoreboard;

    /**
     * Arena constructor.
     * @param int $id
     * @param bool $isPremium
     * @param Level $level
     * @param bool $scoreboardEnabled
     */
    public function __construct(int $id, bool $isPremium, Level $level, bool $scoreboardEnabled = true) {
        parent::__construct($id);

        $this->isPremium = $isPremium;

        $this->level = $level;

        $worldName = 'Match-' . $id;

        $this->worldName = $worldName;

        FileCopyAsyncTask::recurse_copy(
            Duels::getInstance()->getDataFolder() . 'arenas/' . $level->getFolderName(),
            Server::getInstance()->getDataPath() . 'worlds/' . $worldName);

        Server::getInstance()->loadLevel($worldName);

        $level = $this->getWorld();

        if ($level == null) return;

        $level->setTime(pocketLevel::TIME_DAY);
        $level->stopTime();

        $this->bootGame();

        if (!$scoreboardEnabled) return;

        $this->scoreboard = new Scoreboard($this, Translation::getInstance()->translateString('SCOREBOARD_IN_GAME_TITLE'), Scoreboard::SIDEBAR);
    }

    public function bootGame(): void {
        $this->scheduleRepeatingTask(new GameCountDownUpdateTask('game_count_down_update', $this));
    }

    /**
     * @return string
     */
    public function getWorldName(): string {
        return $this->worldName;
    }

    /**
     * @return pocketLevel|null
     */
    public function getWorld(): ?pocketLevel {
        return Server::getInstance()->getLevelByName($this->getWorldName());
    }

    /**
     * @return pocketLevel
     */
    public function getWorldNonNull(): pocketLevel {
        $level = $this->getWorld();

        if ($level == null) {
            throw new PluginException('World not found');
        }

        return $level;
    }

    /**
     * @return Level
     */
    public function getLevel(): Level {
        return $this->level;
    }

    /**
     * @return Scoreboard
     */
    public function getScoreboard(): Scoreboard {
        return $this->scoreboard;
    }

    /**
     * @return int
     */
    public function getStatus(): int {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isPremium(): bool {
        return $this->isPremium;
    }

    /**
     * @return bool
     */
    public function isWaiting(): bool {
        return $this->getStatus() == self::STATUS_WAITING;
    }

    /**
     * @return bool
     */
    public function isStarted(): bool {
        return $this->getStatus() == self::STATUS_IN_GAME;
    }

    /**
     * @return bool
     */
    public function isFinishing(): bool {
        return $this->status == self::STATUS_FINISHING;
    }

    /**
     */
    public function start(): void {
        $this->setStatus(self::STATUS_IN_GAME);

        (new ArenaStartEvent($this))->call();

        $this->broadcastMessage('&aThe match has started, good luck!');

        foreach ($this->sessions as $session) {
            $session->getGeneralPlayer()->setImmobile(false);

            $session->setEnergized(strtolower($this->level->getKitName()) == 'sumo');
        }

        $this->scheduleRepeatingTask(new GameMatchUpdateTask('game_match_update', $this));
    }

    /**
     * @param Session[] $winners
     */
    public function finish(array $winners = []): void {
        $this->setStatus(self::STATUS_FINISHING);

        foreach ($winners as $winner) $winner->handleWin();

        foreach ($this->getAllPlayers() as $player) $player->setResetPlayerAttributes();

        $this->scheduleRepeatingTask(new GameFinishUpdateTask('game_finish_update', $this));
    }

    /**
     * @param Session $session
     */
    public function addSession(Session $session): void {
        $this->sessions[strtolower($session->getName())] = $session;

        (new SessionJoinArenaEvent($session, $this))->call();

        $session->setArena($this);

        Duels::getDefaultScoreboard()->removePlayer($session);

        Duels::getDuelFactory()->removeDuels($session->getName());
    }

    /**
     * @param array<string, Session> $sessions
     */
    public function addSessions(array $sessions): void {
        $this->scoreboard->removePlayer();

        // TODO: Hack for the loadOpponent
        foreach ($sessions as $session) $this->addSession($session);

        $slot = 1;

        foreach ($sessions as $session) {
            Duels::getQueueFactory()->removeSessionFromQueue($session);

            $session->loadOpponent();

            $session->sendMessage("&c&l" . $this->level->getKit()->getName() . " Duel&r\n&4- Map: &c" . $this->level->getFolderName() . "\n&4- Opponent: &c" . $session->getOpponentName());

            $session->setEnergized();

            $session->setSlot($slot++);

            $session->teleport($this->level->getSlotPosition($session->getSlot(), $this->getWorldNonNull()));

            $this->scoreboard->addPlayer($session);
            $this->scoreboard->setLines(Translation::getInstance()->translateArray('IN_GAME_SCOREBOARD', [
                date('d/m/y'),
                $this->getId(),
                $session->getOpponentName(),
                $this->level->getFolderName(),
                $this->level->getKit()->getName()
            ]), $session);

            $session->setDefaultLobbyAttributes();

            $this->level->getKit()->giveKit($session);

            $session->getGeneralPlayer()->setImmobile();
        }
    }

    /**
     * @param Session $session
     */
    public function removeSession(Session $session): void {
        if (!$this->inArenaAsPlayer($session)) return;

        unset($this->sessions[strtolower($session->getName())]);

        if ($this->isStarted() || $this->isFinishing()) return;

        if (count($this->getAllPlayers()) !== 0) return;

        Duels::getInstance()->removeWorld($this->getWorldName());
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getSession(string $name): ?Session {
        return $this->sessions[strtolower($name)] ?? null;
    }

    /**
     * @return array<string, Session>
     */
    public function getSessions(): array {
        return $this->sessions;
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function inArenaAsPlayer(Session $session): bool {
        return $this->getSession($session->getName()) !== null;
    }

    /**
     * @param Session $session
     */
    public function addSpectator(Session $session): void {
        $this->spectators[strtolower($session->getName())] = $session;
    }

    /**
     * @param Session $session
     */
    public function removeSpectator(Session $session): void {
        if (!$this->inArenaAsSpectator($session)) return;

        unset($this->spectators[strtolower($session->getName())]);
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getSpectator(string $name): ?Session {
        return $this->spectators[strtolower($name)] ?? null;
    }

    /**
     * @return array<string, Session>
     */
    public function getSpectators(): array {
        return $this->spectators;
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function inArenaAsSpectator(Session $session): bool {
        return $this->getSpectator($session->getName()) !== null;
    }

    /**
     * @param Session $session
     */
    public function removeSessionOrSpectator(Session $session): void {
        if ($this->inArenaAsPlayer($session)) {
            $this->removeSession($session);
        } else if ($this->inArenaAsSpectator($session)) {
            $this->removeSpectator($session);
        }
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getSessionOrSpectator(string $name): ?Session {
        return $this->getSession($name) ?? $this->getSpectator($name);
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function inArenaAsPlayerOrSpectator(Session $session): bool {
        return $this->inArenaAsPlayer($session) || $this->inArenaAsSpectator($session);
    }

    /**
     * @return Session[]
     */
    public function getAllPlayers(): array {
        return array_merge($this->sessions, $this->getSpectators());
    }

    /**
     * @param string $message
     */
    public function broadcastMessage(string $message): void {
        foreach ($this->getAllPlayers() as $p) {
            $p->sendMessage($message);
        }
    }
}