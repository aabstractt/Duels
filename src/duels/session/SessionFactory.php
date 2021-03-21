<?php

declare(strict_types=1);

namespace duels\session;

use duels\Duels;
use duels\listener\BlockListener;
use duels\listener\EntityListener;
use duels\listener\InventoryListener;
use duels\listener\PlayerInteractListener;
use duels\listener\PlayerListener;
use Exception;
use pocketmine\Player;

class SessionFactory {

    /** @var array<string, Session> */
    private $sessions = [];

    /**
     * SessionFactory constructor.
     */
    public function __construct() {
        Duels::getInstance()->registerListeners(
            new PlayerInteractListener(),
            new PlayerListener(),
            new EntityListener(),
            new BlockListener(),
            new InventoryListener()
        );
    }

    /**
     * @param string $name
     * @throws Exception
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
        $session = $this->getSessionPlayerNullable($player);

        if ($session == null) {
            throw new SessionException('Invalid session for ' . $player->getName());
        }

        return $session;
    }

    /**
     * @param Player $player
     * @return Session|null
     */
    public function getSessionPlayerNullable(Player $player): ?Session {
        return $this->sessions[strtolower($player->getName())] ?? null;
    }

    /**
     * @return Session[]
     */
    public function getDefaultSessions(): array {
        $sessions = [];

        foreach ($this->sessions as $session) {
            $instance = $session->getGeneralPlayerNullable();

            if ($instance == null) continue;

            if ($instance->getLevelNonNull() === Duels::getDefaultLevelNonNull()) {
                $sessions[] = $session;
            }
        }

        return $sessions;
    }

    /**
     * @param Player $player
     */
    public function removeSession(Player $player): void {
        $session = $this->sessions[strtolower($player->getName())] ?? null;

        if ($session == null) return;

        Duels::getQueueFactory()->removeSessionFromQueue($session);

        Duels::getDuelFactory()->removeDuels($player->getName());

        $session->remove(true);

        unset($this->sessions[strtolower($player->getName())]);
    }
}