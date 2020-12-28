<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\kit\Kit;
use duels\session\Session;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class ArenaFactory {

    /** @var array<int, Arena> */
    protected $arenas = [];
    /** @var int */
    private $gamesPlayed = 1;

    /**
     * @param Session[] $sessions
     * @param Level|null $level
     * @return void
     */
    public function createArena(array $sessions, Level $level = null): void {
        if ($level === null) {
            $level = Duels::getLevelFactory()->getRandomLevel();
        }

        try {
            if ($level == null) {
                throw new PluginException('Level not found');
            }

            $arena = Duels::getInstance()->generateNewArena($this->gamesPlayed++, $level);

            $this->arenas[$arena->getId()] = $arena;

            $arena->addSessions($sessions);
        } catch (PluginException $e) {
            Server::getInstance()->getLogger()->logException($e);
        }
    }

    /**
     * @param string $folderName
     * @return Arena[]
     */
    public function getArenas(string $folderName): array {
        /** @var Arena[] $arenas */
        $arenas = [];

        foreach ($this->arenas as $arena) {
            if ($arena->getLevel()->getFolderName() !== $folderName) continue;

            $arenas[$arena->getId()] = $arena;
        }

        return $arenas;
    }

    /**
     * @param Kit $kit
     * @return array<int, Arena>
     */
    public function getKitArenas(Kit $kit): array {
        /** @var array<int, Arena> $arenas */
        $arenas = [];

        foreach ($this->arenas as $arena) {
            if (strtolower($arena->getLevel()->getKit()->getName()) !== strtolower($kit->getName())) continue;

            $arenas[$arena->getId()] = $arena;
        }

        return $arenas;
    }

    /**
     * @param Session $session
     * @return Arena|null
     */
    public function getArena(Session $session): ?Arena {
        foreach ($this->arenas as $arena) {
            if (!$arena->inArenaAsPlayerOrSpectator($session)) continue;

            return $arena;
        }

        return null;
    }

    /**
     * @param int $id
     * @return Arena|null
     */
    public function getArenaId(int $id): ?Arena {
        return $this->arenas[$id] ?? null;
    }

    /**
     * @param int $id
     */
    public function removeArena(int $id): void {
        if (!isset($this->arenas[$id])) return;

        unset($this->arenas[$id]);
    }

    /**
     * @param Session $session
     * @param Session|null $killerSession
     */
    public function handlePlayerDeath(Session $session, ?Session $killerSession): void {
        $arena = $session->getArena();

        if ($arena == null) return;

        $message = '&c' . $session->getName() . '&f was slain';

        if ($killerSession != null) {
            $message .= ' by &c' . $killerSession->getName();
        }

        $arena->broadcastMessage($message);
    }
}