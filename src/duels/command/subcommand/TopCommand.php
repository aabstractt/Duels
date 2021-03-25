<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use duels\utils\LeaderboardEntity;
use Exception;
use pocketmine\level\LevelException;
use pocketmine\utils\TextFormat;

class TopCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        $instance = $session->getGeneralPlayer();

        foreach ($instance->getLevelNonNull()->getEntities() as $entity) {
            if (!$entity instanceof LeaderboardEntity) continue;

            $entity->close();
        }

        $nbt = LeaderboardEntity::createBaseNBT($instance->asVector3(), null, $instance->yaw, $instance->pitch);

        $tag = $instance->namedtag->getTag('Skin');

        if ($tag === null) return;

        $nbt->setTag(clone $tag);

        $entity = LeaderboardEntity::createEntity('LeaderboardEntity', $instance->getLevelNonNull(), $nbt);

        if ($entity == null) {
            throw new LevelException('Entity not found');
        }

        $entity->spawnToAll();

        $entity->setScale(0.1);

        try {
            $text = '&e&l>&r&4 Leaderboard &e&l <';

            $leaderboard = Duels::getInstance()->getProvider()->getLeaderboard();

            if (empty($leaderboard)) $text .= TextFormat::RED . 'Empty';

            foreach ($leaderboard as $i => $targetOffline) {
                $text .= "\n&r&l&6#" . ($i + 1) . ' &r&b' . $targetOffline->getName() . '&f - &e' . $targetOffline->getWins();
            }

            $entity->setNameTag(TextFormat::colorize($text));

            $session->sendMessage(TextFormat::GREEN . 'Successfully created Leaderboard');
        } catch (Exception $e) {
            $session->sendMessage($e->getMessage());

            Duels::getInstance()->getLogger()->logException($e);
        }
    }
}