<?php

declare(strict_types=1);

namespace duels\event\session;

use duels\queue\Queue;
use duels\session\Session;

class SessionJoinQueueEvent extends SessionEvent {

    /** @var Queue */
    private $queue;

    /**
     * SessionJoinQueueEvent constructor.
     * @param Session $session
     * @param Queue $queue
     */
    public function __construct(Session $session, Queue $queue) {
        parent::__construct($session);

        $this->queue = $queue;
    }
}