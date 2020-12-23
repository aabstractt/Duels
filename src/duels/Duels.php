<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use pocketmine\plugin\PluginBase;

class Duels extends PluginBase {

    /** @var Duels */
    private static $instance;
    /** @var LevelFactory */
    private static $levelFactory;
    /** @var QueueFactory */
    private static $queueFactory;
    /** @var ArenaFactory */
    private static $arenaFactory;

    /**
     * @return Duels
     */
    public static function getInstance(): Duels {
        return self::$instance;
    }

    /**
     * @return LevelFactory
     */
    public static function getLevelFactory(): LevelFactory {
        return self::$levelFactory;
    }

    /**
     * @return QueueFactory
     */
    public static function getQueueFactory(): QueueFactory {
        return self::$queueFactory;
    }

    /**
     * @return ArenaFactory
     */
    public static function getArenaFactory(): ArenaFactory {
        return self::$arenaFactory;
    }

    public function onEnable(): void {
        self::$instance = $this;

        self::$levelFactory = new LevelFactory();

        self::$queueFactory = new QueueFactory();

        self::$arenaFactory = new ArenaFactory();
    }

    /**
     * @param int $id
     * @param Level $level
     * @return Arena
     */
    public static function generateNewArena(int $id, Level $level): Arena {
        return new Arena($id, $level);
    }

    /**
     * @param array $data
     * @return Level
     */
    public static function generateNewLevel(array $data): Level {
        return new Level($data);
    }
}