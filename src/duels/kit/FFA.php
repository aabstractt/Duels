<?php

declare(strict_types=1);

namespace duels\kit;

use duels\Duels;
use duels\session\Session;
use pocketmine\level\Level;
use pocketmine\level\LevelException;
use pocketmine\Server;

class FFA {

    /** @var Kit */
    private $kit;
    /** @var string */
    private $worldName;

    /**
     * FFA constructor.
     * @param Kit $kit
     * @param string $worldName
     */
    public function __construct(Kit $kit, string $worldName) {
        $this->kit = $kit;

        $this->worldName = $worldName;
    }

    /**
     * @return Kit
     */
    public function getKit(): Kit {
        return $this->kit;
    }

    /**
     * @param Session $session
     * @param Session|null $killerSession
     */
    public function handlePlayerDeath(Session $session, ?Session $killerSession): void {
        $message = '&c' . $session->getName() . '&f was slain';

        if ($killerSession != null) {
            $message .= ' by &c' . $killerSession->getName();

            $this->kit->giveKit($killerSession);

            $killerSession->getGeneralPlayer()->setHealth($killerSession->getGeneralPlayer()->getMaxHealth());
        }

        $this->broadcastMessage($message);

        $session->teleport(Duels::getDefaultLevelNonNull()->getSafeSpawn());
    }

    /**
     * @param Session $session
     */
    public function join(Session $session): void {
        Duels::getQueueFactory()->removeSessionFromQueue($session);

        $session->teleport($this->getWorld()->getSpawnLocation());

        $session->setDefaultLobbyAttributes();

        $session->setEnergized();

        $this->kit->giveKit($session);
    }

    public function close(): void {
        foreach ($this->getWorld()->getPlayers() as $player) {
            $session = Duels::getSessionFactory()->getSessionPlayerNullable($player);

            if ($session == null) continue;

            $player->teleport(Duels::getDefaultLevelNonNull()->getSpawnLocation());

            $session->setDefaultLobbyAttributes();
        }
    }

    /**
     * @return Level
     */
    public function getWorld(): Level {
        if (!Server::getInstance()->isLevelLoaded($this->worldName)) Server::getInstance()->loadLevel($this->worldName);

        $level = Server::getInstance()->getLevelByName($this->worldName);

        if ($level == null) {
            throw new LevelException('Level not found');
        }

        return $level;
    }

    /**
     * @param string $message
     */
    public function broadcastMessage(string $message): void {
        foreach ($this->getWorld()->getPlayers() as $player) {
            $session = Duels::getSessionFactory()->getSessionPlayerNullable($player);

            if ($session == null) continue;

            $session->sendMessage($message);
        }
    }
}