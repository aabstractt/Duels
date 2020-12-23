<?php

namespace duels\arena;

use duels\asyncio\FileCopyAsyncTask;
use duels\Duels;
use duels\session\Session;
use duels\task\TaskHandlerStorage;
use duels\utils\BossBar;
use duels\utils\Scoreboard;
use pocketmine\level\Level as pocketLevel;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Arena extends TaskHandlerStorage {

    /** @var int */
    public const STATUS_WAITING = 1;
    public const STATUS_STARTING = 2;
    public const STATUS_FULL = 3;
    public const STATUS_IN_GAME = 4;
    public const STATUS_FINISHING = 5;

    /** @var Level */
    protected $level;
    /** @var string */
    protected $worldName;
    /** @var Session[] */
    protected $players = [];
    /** @var Session[] */
    protected $spectators = [];
    /** @var int */
    protected $status = self::STATUS_WAITING;
    /** @var Scoreboard */
    protected $scoreboard;
    /** @var BossBar|null */
    protected $bossbar = null;

    /**
     * Arena constructor.
     * @param int $id
     * @param Level $level
     * @param bool $scoreboardEnabled
     */
    public function __construct(int $id, Level $level, bool $scoreboardEnabled = true) {
        parent::__construct($id);

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

        $this->scoreboard = new Scoreboard($this,
            TextFormat::AQUA . TextFormat::BOLD . strtoupper(Duels::getInstance()->getName()),
            Scoreboard::SIDEBAR);
    }

    public function bootGame(): void {

    }

    /**
     * @return string
     */
    public function getWorldName(): string {
        return $this->worldName;
    }

    /**
     * @return pocketLevel
     */
    public function getWorld(): pocketLevel {
        $level = Server::getInstance()->getLevelByName($this->getWorldName());

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
     * @return BossBar|null
     */
    public function getBossbar(): ?BossBar {
        return $this->bossbar;
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
    public function isStarted(): bool {
        return $this->status == self::STATUS_IN_GAME;
    }

    /**
     * @return bool
     */
    public function isFinishing(): bool {
        return $this->status == self::STATUS_FINISHING;
    }

    /**
     * @param Session $session
     */
    public function addPlayer(Session $session): void {
        $this->players[strtolower($session->getName())] = $session;

        /*if (Game::getInstance()->hasWaitingLobby()) {
            $player->teleport($this->getWaitingLobby());
        }*/

        $this->broadcastMessage('&7' . $session->getName() . '&a has joined the game! &7(&6' . count($this->players) . '&7/&6' . $this->level->getMaxSlots() . '&7)');
    }

    /**
     * @param string $name
     */
    public function removePlayer(string $name): void {
        if (!$this->inArenaAsPlayer($name)) return;

        unset($this->players[strtolower($name)]);

        if ($this->isStarted() || $this->isFinishing()) return;

        $this->broadcastMessage('&7' . $name . '&a has left the game! &7(&6' . count($this->players) . '&7/&6' . $this->level->getMaxSlots() . '&7)');

        if (count($this->getAllPlayers()) !== 0) return;

        //Duels::getInstance()->removeWorld($this->getWorldName());
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getPlayer(string $name): ?Session {
        return $this->players[strtolower($name)] ?? null;
    }

    /**
     * @return array<string, Session>
     */
    public function getPlayers(): array {
        return $this->players;
    }

    /**
     * @param string $name
     * @return bool
     */
    public function inArenaAsPlayer(string $name): bool {
        return $this->getPlayer($name) !== null;
    }

    /**
     * @param Session $session
     */
    public function addSpectator(Session $session): void {
        $this->spectators[strtolower($session->getName())] = $session;

        //$player->setDefaultPlayerAttributes();
    }

    /**
     * @param string $name
     */
    public function removeSpectator(string $name): void {
        if (!$this->inArenaAsSpectator($name)) return;

        unset($this->spectators[strtolower($name)]);
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
     * @param string $name
     * @return bool
     */
    public function inArenaAsSpectator(string $name): bool {
        return $this->getSpectator($name) !== null;
    }

    /**
     * @param string $name
     */
    public function removePlayerOrSpectator(string $name): void {
        if ($this->inArenaAsPlayer($name)) {
            $this->removePlayer($name);
        } else if ($this->inArenaAsSpectator($name)) {
            $this->removeSpectator($name);
        }
    }

    /**
     * @param string $name
     * @return Session|null
     */
    public function getPlayerOrSpectator(string $name): ?Session {
        return $this->getPlayer($name) ?? $this->getSpectator($name);
    }

    /**
     * @param string $name
     * @return bool
     */
    public function inArenaAsPlayerOrSpectator(string $name): bool {
        return $this->inArenaAsPlayer($name) || $this->inArenaAsSpectator($name);
    }

    /**
     * @return Session[]
     */
    public function getAllPlayers(): array {
        return array_merge($this->players, $this->getSpectators());
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