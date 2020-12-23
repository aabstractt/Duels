<?php

declare(strict_types=1);

namespace duels\session;

use duels\arena\Arena;
use duels\math\GameVector3;
use pocketmine\level\Level as pocketLevel;
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

    /**
     * Session constructor.
     * @param string $name
     */
    public function __construct(string $name) {
        $this->name = $name;
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
     * @param Arena|null $arena
     */
    public function setArena(Arena $arena = null): void {
        $this->arena = $arena;
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
     * @param pocketLevel|null $level
     * @return bool
     */
    public function isInsideArena(pocketLevel $level = null): bool {
        $arena = $this->arena;

        if ($level == null) $level = $arena->getWorld();

        if ($level == null) return false;

        return $level->getFolderName() == $arena->getWorldName() || $level->getFolderName() == 'Match-' . $arena->getId();
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
        return $this->getArena()->inArenaAsSpectator($this->getName());
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
}