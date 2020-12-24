<?php

declare(strict_types=1);

namespace duels\session;

use duels\Duels;
use duels\listener\EntityListener;
use duels\listener\PlayerListener;
use pocketmine\Player;

class SessionFactory {

    /** @var array<string, Session> */
    private $sessions = [];

    /**
     * SessionFactory constructor.
     */
    public function __construct() {
        Duels::getInstance()->registerListeners(
            new PlayerListener(),
            new EntityListener()
        );
    }

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
     * @param string $name
     * @return Session|null
     */
    public function getSessionPlayerNullable(string $name): ?Session {
        return $this->sessions[strtolower($name)] ?? null;
    }

    /**
     * @param Player $player
     */
    public function removeSession(Player $player): void {
        $session = $this->sessions[strtolower($player->getName())] ?? null;

        if ($session == null) return;

        Duels::getQueueFactory()->removeSessionFromQueue($session);

        $session->remove(true);

        unset($this->sessions[strtolower($player->getName())]);
    }
}