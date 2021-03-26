<?php

declare(strict_types=1);

namespace duels\event\arena;

use duels\arena\Arena;
use pocketmine\event\Event;

class ArenaStartEvent extends Event {

    /** @var Arena */
    private $arena;

    /**
     * ArenaStartEvent constructor.
     * @param Arena $arena
     */
    public function __construct(Arena $arena) {
        $this->arena = $arena;
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena {
        return $this->arena;
    }
}