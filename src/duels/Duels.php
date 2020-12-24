<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\kit\KitFactory;
use duels\listener\PlayerListener;
use duels\session\SessionFactory;
use pocketmine\event\Listener;
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
    /** @var SessionFactory */
    private static $sessionFactory;
    /** @var KitFactory */
    private static $kitFactory;

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

    /**
     * @return SessionFactory
     */
    public static function getSessionFactory(): SessionFactory {
        return self::$sessionFactory;
    }

    /**
     * @return KitFactory
     */
    public static function getKitFactory(): KitFactory {
        return self::$kitFactory;
    }

    public function onEnable(): void {
        self::$instance = $this;

        self::$levelFactory = new LevelFactory();

        self::$queueFactory = new QueueFactory();

        self::$arenaFactory = new ArenaFactory();

        self::$sessionFactory = new SessionFactory();

        $this->registerListeners(new PlayerListener());
    }

    /**
     * @param Listener ...$listeners
     */
    public function registerListeners(Listener ...$listeners): void {
        foreach ($listeners as $listener) {
            $this->getServer()->getPluginManager()->registerEvents($listener, $this);
        }
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