<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use duels\math\GameVector3;
use duels\utils\LeaderboardEntity;
use Exception;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\level\LevelUnloadEvent;
use pocketmine\event\Listener;
use pocketmine\level\LevelException;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LevelListener implements Listener {

    /**
     * @param LevelLoadEvent $ev
     *
     * @priority HIGHEST
     * @throws Exception
     */
    public function onLevelLoadEvent(LevelLoadEvent $ev): void {
        $level = $ev->getLevel();

        $data = Duels::getInstance()->getConfig()->get('entity-leaderboard', []);

        if (empty($data)) return;

        if (!Server::getInstance()->hasOfflinePlayerData($data['username'])) return;

        if ($level !== Duels::getDefaultLevelNonNull()) return;

        $compound = Server::getInstance()->getOfflinePlayerData($data['username']);

        $tag = $compound->getTag('Skin');

        if ($tag == null) return;

        $nbt = LeaderboardEntity::createBaseNBT(GameVector3::fromArray($data)->get(), null, $data['yaw'], $data['pitch']);

        $nbt->setTag(clone $tag);

        $entity = LeaderboardEntity::createEntity('LeaderboardEntity', $level, $nbt);

        if ($entity == null) {
            throw new LevelException('Entity not found');
        }

        $entity->setScale(0.1);

        $text = '&e&l>&r&4 Leaderboard &e&l <';

        $leaderboard = Duels::getInstance()->getProvider()->getLeaderboard();

        if (empty($leaderboard)) $text .= TextFormat::RED . 'Empty';

        foreach ($leaderboard as $i => $targetOffline) {
            $text .= "\n&r&l&6#" . ($i + 1) . ' &r&b' . $targetOffline->getName() . '&f - &e' . $targetOffline->getWins();
        }

        $entity->setNameTag(TextFormat::colorize($text));
    }

    /**
     * @param LevelUnloadEvent $ev
     *
     * @priority HIGHEST
     */
    public function onLevelUnloadEvent(LevelUnloadEvent $ev): void {
        $level = $ev->getLevel();

        foreach ($level->getEntities() as $entity) {
            if (!$entity instanceof LeaderboardEntity) continue;

            $entity->close();
        }
    }
}