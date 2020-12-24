<?php

declare(strict_types=1);

namespace duels\session;

use duels\Duels;
use pocketmine\Player;

class SessionFactory {

    /** @var array<string, Session> */
    private $sessions = [];

    /**
     * @param string $name
     */
    public function createSession(string $name): void {
        $session = $this->sessions[strtolower($name)] = new Session($name);

        $session->setDefaultLobbyAttributes();
    }

    /**
     * @param Player $player
     * @return Session
     */
    public function getSessionPlayer(Player $player): Session {
        $session = $this->sessions[strtolower($player->getName())] ?? null;

        if ($session == null) {
            throw new SessionException('Invalid session for ' . $player->getName());
        }

        return $session;
    }

    /**
     * @param Player $player
     */
    public function removeSession(Player $player): void {
        $session = $this->sessions[strtolower($player->getName())] ?? null;

        if ($session == null) return;

        Duels::getQueueFactory()->removeSessionFromQueue($session);

        unset($this->sessions[strtolower($player->getName())]);
    }
}