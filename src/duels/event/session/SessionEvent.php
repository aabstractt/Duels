<?php

declare(strict_types=1);

namespace duels\event\session;

use duels\session\Session;
use pocketmine\event\Event;

class SessionEvent extends Event {

    /** @var Session */
    private $session;

    /**
     * SessionEvent constructor.
     * @param Session $session
     */
    public function __construct(Session $session) {
        $this->session = $session;
    }

    /**
     * @return Session
     */
    public function getSession(): Session {
        return $this->session;
    }
}