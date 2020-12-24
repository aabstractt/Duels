<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\session\Session;
use pocketmine\Player as pocketPlayer;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class ArenaFactory {

    /** @var array<int, Arena> */
    protected $arenas = [];
    /** @var int */
    private $gamesPlayed = 1;

    /**
     * @param array<string, Session> $players
     * @param Level|null $level
     * @return void
     */
    public function createArena(array $players, Level $level = null): void {
        if ($level === null) {
            $level = Duels::getLevelFactory()->getRandomLevel();
        }

        try {
            if ($level == null) {
                throw new PluginException('Level not found');
            }

            $arena = Duels::getInstance()->generateNewArena($this->gamesPlayed++, $level);

            $this->arenas[$arena->getId()] = $arena;

            foreach ($players as $player) {
                $arena->addPlayer($player);
            }
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
     * @param pocketPlayer $player
     * @return Arena|null
     */
    public function getArena(pocketPlayer $player): ?Arena {
        foreach ($this->arenas as $arena) {
            if (!$arena->inArenaAsPlayerOrSpectator($player->getName())) continue;

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
}