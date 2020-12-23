<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Level;
use pocketmine\utils\Config;

class LevelFactory {


    /** @var Level[] */
    private $levels = [];

    public function init(): void {
        foreach ((new Config(Duels::getInstance()->getDataFolder() . 'levels.json', Config::JSON))->getAll() as $data) {
            $this->loadLevel($data);
        }
    }

    /**
     * @param Level $level
     */
    public function saveLevel(Level $level): void {
        $config = new Config(Duels::getInstance()->getDataFolder() . 'levels.json', Config::JSON);

        $config->set($level->getFolderName(), $level->getData());

        $config->save();
    }

    /**
     * @param array $data
     * @return Level
     */
    public function loadLevel(array $data): Level {
        return $this->levels[strtolower($data['folderName'])] = Duels::getInstance()->generateNewLevel($data);
    }

    /**
     * @param string $folderName
     * @return Level|null
     */
    public function getLevel(string $folderName): ?Level {
        return $this->levels[strtolower($folderName)] ?? null;
    }

    /**
     * @return Level[]
     */
    public function getAllLevels(): array {
        return $this->levels;
    }

    /**
     * @return Level|null
     */
    public function getRandomLevel(): ?Level {
        /** @var Level|null $betterLevel */
        $betterLevel = null;

        $arenasCount = PHP_INT_MAX;

        foreach ($this->levels as $level) {
            if ($betterLevel == null) {
                $betterLevel = $level;

                continue;
            }

            if (($count = count(Duels::getArenaFactory()->getArenas($level->getFolderName()))) >= $arenasCount) continue;

            $betterLevel = $level;

            $arenasCount = $count;
        }

        return $betterLevel;
    }
}