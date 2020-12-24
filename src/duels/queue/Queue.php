<?php

declare(strict_types=1);

namespace duels\queue;

use duels\Duels;
use duels\kit\Kit;
use duels\session\Session;

class Queue {

    /** @var Kit */
    private $kit;
    /** @var bool */
    private $isPremium;
    /** @var array<string, Session> */
    private $sessions = [];

    /**
     * Queue constructor.
     * @param Kit $kit
     * @param bool $isPremium
     */
    public function __construct(Kit $kit, bool $isPremium) {
        $this->kit = $kit;

        $this->isPremium = $isPremium;
    }

    /**
     * @return Kit
     */
    public function getKit(): Kit {
        return $this->kit;
    }

    /**
     * @return bool
     */
    public function isPremium(): bool {
        return $this->isPremium;
    }

    /**
     * @param Session $session
     */
    public function addSession(Session $session): void {
        $this->sessions[strtolower($session->getName())] = $session;
    }

    /**
     * @param Session $session
     */
    public function removeSession(Session $session): void {
        if (empty($this->sessions[strtolower($session->getName())])) return;

        unset($this->sessions[strtolower($session->getName())]);
    }

    public function update(): void {
        $sessionsAvailable = [];

        $timeWaiting = 0;

        foreach ($this->sessions as $session) {
            if (count($sessionsAvailable) == 2) continue;

            if (isset($sessionsAvailable[strtolower($session->getName())])) continue;

            if ($timeWaiting > $session->getQueueWaitingTime()) continue;

            if ($session->getArena() !== null) continue;

            $sessionsAvailable[strtolower($session->getName())] = $session;
        }

        Duels::getArenaFactory()->createArena($sessionsAvailable);
    }
}