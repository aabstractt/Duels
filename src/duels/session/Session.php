<?php

declare(strict_types=1);

namespace duels\session;

use duels\api\Form;
use duels\arena\Arena;
use duels\Duels;
use duels\math\GameVector3;
use duels\provider\TargetOffline;
use duels\utils\ItemUtils;
use Exception;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class Session {

    /** @var string */
    private $name;
    /** @var Arena|null */
    private $arena;
    /** @var bool */
    private $energized = false;
    /** @var int */
    private $queueWaitingTime = 0;
    /** @var bool */
    private $lobbyItemsEnabled = true;
    /** @var string|null */
    private $opponentName = null;
    /** @var int */
    private $slot = 0;
    /** @var string */
    private $lastKiller;
    /** @var int */
    private $lastKillerTime = -1;
    /** @var string */
    private $lastAssistance;
    /** @var int */
    private $lastAssistanceTime = -1;
    /** @var TargetOffline */
    private $targetOffline;

    /**
     * Session constructor.
     * @param string $name
     * @throws Exception
     */
    public function __construct(string $name) {
        $this->name = $name;

        $this->targetOffline = Duels::getInstance()->getProvider()->getTargetOffline($name) ?? new TargetOffline(['username' => $name]);
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return Arena|null
     */
    public function getArena(): ?Arena {
        return $this->arena;
    }

    /**
     * @return Player
     */
    public function getGeneralPlayer(): Player {
        $player = Server::getInstance()->getPlayerExact($this->getName());

        if ($player == null) {
            throw new PluginException('Player not was found');
        }

        return $player;
    }

    /**
     * @return string
     */
    public function getOpponentName(): string {
        return $this->opponentName ?? 'null';
    }

    /**
     * @return Session|null
     */
    public function getOpponentPlayer(): ?Session {
        $opponent = Server::getInstance()->getPlayerExact($this->getOpponentName());

        if ($opponent == null) {
            return null;
        }

        return Duels::getSessionFactory()->getSessionPlayerNullable($opponent);
    }

    /**
     * @return Level
     */
    public function getLevelNonNull(): Level {
        return $this->getGeneralPlayer()->getLevelNonNull();
    }

    /**
     * @return int
     */
    public function getQueueWaitingTime(): int {
        return $this->queueWaitingTime;
    }

    /**
     * @param Arena|null $arena
     */
    public function setArena(Arena $arena = null): void {
        $this->arena = $arena;

        if ($arena == null) $this->opponentName = null;
    }

    /**
     * @return Session|null
     */
    public function getLastKiller(): ?Session {
        if ($this->lastKiller == null || $this->lastKillerTime == -1) return null;

        $arena = $this->arena;

        if ($arena == null) return null;

        $lastKiller = $arena->getSessionOrSpectator($this->lastKiller);

        if ($lastKiller == null) return null;

        if (time() - $this->lastKillerTime > 10) return null;

        if (!$lastKiller->isConnected()) return null;

        return $lastKiller;
    }

    /**
     * @return Session|null
     */
    public function getLastAssistance(): ?Session {
        if ($this->lastAssistance == null || $this->lastAssistanceTime == -1) return null;

        $arena = $this->arena;

        if ($arena == null) return null;

        $lastAssistance = $arena->getSessionOrSpectator($this->lastAssistance);

        if ($lastAssistance == null) return null;

        if (time() - $this->lastAssistanceTime > 10) return null;

        if (!$lastAssistance->isConnected()) return null;

        return $lastAssistance;
    }

    /**
     * @param Session $session
     */
    public function attack(Session $session): void {
        if ($this->lastKiller === null) {
            $this->lastKiller = $session->getName();

            return;
        }

        if (strtolower($session->getName()) !== strtolower($this->lastKiller)) {
            $this->lastAssistance = $this->lastKiller;

            $this->lastAssistanceTime = time();

            $this->lastKiller = $session->getName();
        }

        $this->lastKillerTime = time();
    }

    /**
     * @param Vector3|GameVector3 $pos
     */
    public function teleport($pos): void {
        if ($pos instanceof GameVector3) {
            $pos = $pos->get();
        }

        $this->getGeneralPlayer()->teleport($pos);
    }

    /**
     * @param int $queueWaitingTime
     */
    public function increaseQueueWaitingTime(int $queueWaitingTime = 0): void {
        if ($queueWaitingTime == 1) {
            $this->queueWaitingTime = 0;

            return;
        }

        $this->queueWaitingTime++;
    }

    /**
     * @param bool $lobbyItemsEnabled
     */
    public function setLobbyItemsEnabled(bool $lobbyItemsEnabled = false): void {
        $this->lobbyItemsEnabled = $lobbyItemsEnabled;
    }

    /**
     * @return bool
     */
    public function hasLobbyItemsEnabled(): bool {
        return $this->lobbyItemsEnabled;
    }

    /**
     * @param bool $value
     */
    public function setEnergized(bool $value = true): void {
        $this->energized = $value;
    }

    /**
     * @return bool
     */
    public function isEnergized(): bool {
        return $this->energized;
    }

    /**
     * @param bool $value
     */
    public function setImmobile(bool $value = true): void {
        $this->getGeneralPlayer()->setImmobile($value);
    }

    /**
     * @return bool
     */
    public function isImmobile(): bool {
        return $this->getGeneralPlayer()->isImmobile();
    }

    /**
     * @param Level|null $level
     * @return bool
     */
    public function isInsideArena(Level $level = null): bool {
        $arena = $this->arena;

        if ($arena == null) return false;

        if ($level == null) $level = $arena->getWorldNonNull();

        return $level->getFolderName() == $arena->getWorldName() || $level->getFolderName() == 'Match-' . $arena->getId();
    }

    /**
     * @return bool
     */
    public function inArena(): bool {
        return $this->arena != null;
    }

    /**
     * @return bool
     */
    public function isConnected(): bool {
        return Server::getInstance()->getPlayerExact($this->getName()) !== null;
    }

    /**
     * @return bool
     */
    public function isSpectator(): bool {
        $arena = $this->getArena();

        if ($arena == null) return false;

        return $arena->inArenaAsSpectator($this);
    }

    /**
     * @param string $message
     */
    public function sendMessage(string $message): void {
        $this->getGeneralPlayer()->sendMessage(TextFormat::colorize($message));
    }

    /**
     * @param string $title
     * @param string $subtitle
     */
    public function sendTitle(string $title, string $subtitle = ''): void {
        $this->getGeneralPlayer()->addTitle(TextFormat::colorize($title), TextFormat::colorize($subtitle));
    }

    /**
     * @param callable $callback
     * @param array $data
     */
    public function sendForm(callable $callback, array $data): void {
        $this->getGeneralPlayer()->sendForm(new Form($callback, $data));
    }

    public function loadOpponent(): void {
        $arena = $this->arena;

        if ($arena == null) return;

        foreach ($arena->getSessions() as $session) {
            if (strtolower($session->getName()) == strtolower($this->getName())) continue;

            $this->opponentName = $session->getName();
        }
    }

    /**
     * @param int $slot
     */
    public function setSlot(int $slot): void {
        $this->slot = $slot;
    }

    /**
     * @return int
     */
    public function getSlot(): int {
        return $this->slot;
    }

    /**
     * @return TargetOffline
     */
    public function getTargetOffline(): TargetOffline {
        return $this->targetOffline;
    }

    /**
     * Give the default attributes in the lobby or when join a game
     */
    public function setDefaultLobbyAttributes(): void {
        $instance = $this->getGeneralPlayer();

        $instance->getInventory()->clearAll();
        $instance->getArmorInventory()->clearAll();

        $instance->setHealth($instance->getMaxHealth());
        $instance->setFood($instance->getMaxFood());

        $instance->removeAllEffects();

        $instance->setAllowFlight(false);
        $instance->setFlying(false);

        $instance->setGamemode($instance::SURVIVAL);

        $this->setImmobile(false);

        if ($this->getLevelNonNull() !== Server::getInstance()->getDefaultLevel() || !$this->lobbyItemsEnabled) return;

        $instance->getInventory()->setContents(ItemUtils::getLobbyItems());
    }

    public function setResetPlayerAttributes(): void {
        $this->setDefaultLobbyAttributes();

        $instance = $this->getGeneralPlayer();

        $instance->setAllowFlight(true);
        $instance->setFlying(true);
    }

    /**
     * Send the player won values
     */
    public function handleWin(): void {
        $this->sendTitle('&c&lGame finished!', '&aYou won!');

        $arena = $this->getArena();

        if ($arena == null) return;

        $this->targetOffline->increaseWins();

        $arena->broadcastMessage('&c' . $this->getName() . ' &fwon the &c' . $arena->getLevel()->getKit()->getName() . '&4 duel!');
    }

    /**
     * @param bool $teleport
     */
    public function remove(bool $teleport = false): void {
        $arena = $this->arena;

        if ($arena == null) return;

        $arena->getScoreboard()->removePlayer($this);

        $arena->removeSessionOrSpectator($this);

        if (!$teleport) {
            $arena->addSpectator($this);

            return;
        }

        if (!$this->isSpectator()) Duels::getArenaFactory()->handlePlayerDeath($this, $this->getLastKiller());

        $this->setArena();

        $this->teleport(Duels::getDefaultLevelNonNull()->getSpawnLocation());

        $this->setDefaultLobbyAttributes();

        try {
            Duels::getInstance()->getProvider()->setTargetOffline($this->targetOffline);
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);

            $this->getGeneralPlayer()->kick($e->getMessage());
        }
    }
}