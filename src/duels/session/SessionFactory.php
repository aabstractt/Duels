<?php

declare(strict_types=1);

namespace duels\session;

use pocketmine\Player;

class SessionFactory {

    /** @var array<string, Session> */
    private $sessions = [];

    /**
     * @param string $name
     */
    public function createSession(string $name): void {
        $this->sessions[strtolower($name)] = new Session($name);
    }

    /**
     * @param Player $player
     * @return Session|null
     */
    public function getSessionPlayer(Player $player): ?Session {
        return $this->sessions[strtolower($player->getName())] ?? null;
    }

    /**
     * @param Player $player
     */
    public function removeSession(Player $player): void {
        if (empty($this->sessions[strtolower($player->getName())])) return;

        unset($this->sessions[strtolower($player->getName())]);
    }
}