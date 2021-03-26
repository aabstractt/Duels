<?php

declare(strict_types=1);

namespace duels\event\session;

use duels\arena\Arena;
use duels\session\Session;

class SessionJoinArenaEvent extends SessionEvent {

    /** @var Arena */
    private $arena;

    /**
     * SessionJoinArenaEvent constructor.
     * @param Session $session
     * @param Arena $arena
     */
    public function __construct(Session $session, Arena $arena) {
        parent::__construct($session);

        $this->arena = $arena;
    }

    /**
     * @return Arena
     */
    public function getArena(): Arena {
        return $this->arena;
    }
}