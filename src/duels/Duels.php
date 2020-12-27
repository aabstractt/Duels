<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\asyncio\FileDeleteAsyncTask;
use duels\command\ConfigCommand;
use duels\kit\KitFactory;
use duels\queue\Queue;
use duels\queue\QueueFactory;
use duels\session\SessionFactory;
use pocketmine\event\Listener;
use pocketmine\level\Level as pocketLevel;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

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

    /**
     * @param string $worldName
     */
    public function removeWorld(string $worldName): void {
        if (($level = $this->getServer()->getLevelByName($worldName)) !== null) {
            $this->getServer()->unloadLevel($level);
        }

        $this->getServer()->getAsyncPool()->submitTask(new FileDeleteAsyncTask($this->getServer()->getDataPath() . '/worlds/' . $worldName));
    }

    public function onEnable(): void {
        if (!is_dir($this->getDataFolder())) @mkdir($this->getDataFolder());
        if (!is_dir($this->getDataFolder() . 'arenas/')) @mkdir($this->getDataFolder() . 'arenas/');

        $this->saveConfig();

        self::$instance = $this;

        self::$queueFactory = new QueueFactory();

        self::$kitFactory = new KitFactory();

        self::$levelFactory = new LevelFactory();

        if (file_exists($this->getDataFolder() . 'levels.json')) {
            self::$levelFactory->init();
        }

        self::$arenaFactory = new ArenaFactory();

        self::$sessionFactory = new SessionFactory();

        $this->getServer()->getCommandMap()->register(ConfigCommand::class, new ConfigCommand());
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

    /**
     * @return pocketLevel
     */
    public static function getDefaultLevelNonNull(): pocketLevel {
        $level = Server::getInstance()->getDefaultLevel();

        if ($level == null) {
            throw new PluginException('Default world is null');
        }

        return $level;
    }

    /**
     * @param string $kitName
     * @param string $placeholder
     */
    public function addPlaceHolder(string $kitName, string $placeholder): void {
        $config = $this->getConfig();

        $config->set('placeHolders', array_merge($config->get('placeHolders'), [$kitName => $placeholder]));

        $config->save();
    }

    /**
     * @param Queue $queue
     * @return string
     */
    public static function translatePlaceHolder(Queue $queue): string {
        return '';
    }

    /**
     * @return bool
     */
    public static function isLobbyItemsEnabled(): bool {
        return self::$instance->getConfig()->get('lobby-items-enabled', false);
    }

    /**
     * @return bool
     */
    public static function isSpectatorItemsEnabled(): bool {
        return self::$instance->getConfig()->get('spectator-items-enabled', false);
    }
}