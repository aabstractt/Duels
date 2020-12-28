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
    public function __construct(Kit $kit, bool $isPremium = true) {
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
     * @return Session[]
     */
    public function getSessions(): array {
        return $this->sessions;
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function hasSession(Session $session): bool {
        return isset($this->sessions[strtolower($session->getName())]);
    }

    /**
     * @param Session $session
     * @return bool
     */
    public function addSession(Session $session): bool {
        if (isset($this->sessions[strtolower($session->getName())])) return false;

        Duels::getQueueFactory()->removeSessionFromQueue($session);

        $session->increaseQueueWaitingTime(1);

        $this->sessions[strtolower($session->getName())] = $session;

        return true;
    }

    /**
     * @param Session $session
     */
    public function removeSession(Session $session): void {
        if (empty($this->sessions[strtolower($session->getName())])) return;

        unset($this->sessions[strtolower($session->getName())]);
    }

    /**
     * Search a opponent and arena
     */
    public function update(): void {
        if (count($this->sessions) < 2) return;

        $sessionsAvailable = [];

        $timeWaiting = 0;

        foreach ($this->sessions as $session) $session->increaseQueueWaitingTime();

        $intents = 0;

        while (count($sessionsAvailable) < 2 && $intents < 3) {
            foreach ($this->sessions as $session) {
                if (isset($sessionsAvailable[strtolower($session->getName())])) continue;

                if ($timeWaiting > $session->getQueueWaitingTime()) continue;

                if ($session->getArena() !== null) continue;

                $timeWaiting = 0;

                $sessionsAvailable[strtolower($session->getName())] = $session;

                $session->increaseQueueWaitingTime(1);
            }

            $intents++;
        }

        if (count($sessionsAvailable) < 2) return;

        Duels::getArenaFactory()->createArena($sessionsAvailable, $this->isPremium());
    }
}