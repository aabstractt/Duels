<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\asyncio\FileDeleteAsyncTask;
use duels\command\ConfigCommand;
use duels\command\LeaveCommand;
use duels\duel\DuelFactory;
use duels\kit\KitFactory;
use duels\provider\MysqlProvider;
use duels\queue\Queue;
use duels\queue\QueueFactory;
use duels\session\SessionFactory;
use duels\translation\Translation;
use duels\utils\LeaderboardEntity;
use duels\utils\Scoreboard;
use Exception;
use pocketmine\event\Listener;
use pocketmine\level\Level as pocketLevel;
use pocketmine\plugin\PluginBase;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

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
    /** @var DuelFactory */
    private static $duelFactory;
    /** @var Scoreboard */
    private static $scoreboard;
    /** @var MysqlProvider */
    private $provider;

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
     * @return DuelFactory
     */
    public static function getDuelFactory(): DuelFactory {
        return self::$duelFactory;
    }

    /**
     * @return Scoreboard
     */
    public static function getDefaultScoreboard(): Scoreboard {
        return self::$scoreboard;
    }

    /**
     * @return MysqlProvider
     */
    public function getProvider(): MysqlProvider {
        return $this->provider;
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

        try {
            $this->provider = new MysqlProvider($this->getConfig()->get('mysql'));
        } catch (Exception $e) {
            $this->getLogger()->logException($e);

            $this->getServer()->getPluginManager()->disablePlugin($this);

            return;
        }

        $matches = glob($this->getServer()->getDataPath() . 'worlds/Match-*', GLOB_ONLYDIR);

        if ($matches !== false) {
            foreach ($matches as $match) FileDeleteAsyncTask::recurse_delete($match);
        }

        self::$sessionFactory = new SessionFactory();

        self::$queueFactory = new QueueFactory();

        self::$kitFactory = new KitFactory();

        self::$levelFactory = new LevelFactory();

        if (file_exists($this->getDataFolder() . 'levels.json')) {
            self::$levelFactory->init();
        }

        Translation::getInstance()->init();

        self::$arenaFactory = new ArenaFactory();

        self::$duelFactory = new DuelFactory();

        self::$scoreboard = new Scoreboard(null, Translation::getInstance()->translateString('LOBBY_SCOREBOARD_TITLE'), Scoreboard::SIDEBAR, Scoreboard::ASCENDING);

        $this->getServer()->getCommandMap()->register(ConfigCommand::class, new ConfigCommand());
        $this->getServer()->getCommandMap()->register(LeaveCommand::class, new LeaveCommand('leave', 'Go to the lobby'));

        LeaderboardEntity::registerEntity(LeaderboardEntity::class, true);
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
     * @param bool $isPremium
     * @param Level $level
     * @return Arena
     */
    public static function generateNewArena(int $id, bool $isPremium, Level $level): Arena {
        return new Arena($id, $isPremium, $level);
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

        $config->set('placeHolders', array_merge($config->get('placeHolders', []), [$kitName => ['text' => $placeholder]]));

        $config->save();
    }

    /**
     * @param Queue $queue
     * @return array
     */
    public static function translatePlaceHolder(Queue $queue): array {
        $placeHolders = self::$instance->getConfig()->get('placeHolders', []);

        $data = $placeHolders[$queue->getKit()->getName()] ?? '';

        $data['text'] = TextFormat::colorize(str_replace(['{0}', '{1}'], [count($queue->getSessions()), count(Duels::getArenaFactory()->getKitSessions($queue->getKit(), $queue->isPremium()))], $data['text']));

        return $data;
    }

    /**
     * @return bool
     */
    public static function isQueuePremiumEnabled(): bool {
        return self::$instance->getConfig()->get('queue-premium-enabled', false);
    }

    /**
     * @return bool
     */
    public static function isLobbyItemsEnabled(): bool {
        return self::$instance->getConfig()->get('lobby-items-enabled', false);
    }
}