<?php

declare(strict_types=1);

namespace duels\utils;

use duels\Duels;
use Exception;
use pocketmine\entity\Human;
use pocketmine\utils\TextFormat;

class LeaderboardEntity extends Human {

    /**
     * @return string
     */
    public function getName(): string {
        return 'Leaderboard';
    }

    public function initEntity(): void {
        parent::initEntity();

        $this->setScale(0.1);

        $text = '&e&l>&r&4 Leaderboard &e&l <';

        try {
            $leaderboard = Duels::getInstance()->getProvider()->getLeaderboard();

            if (empty($leaderboard)) $text .= TextFormat::RED . 'Empty';

            foreach ($leaderboard as $i => $targetOffline) {
                $text .= "\n&r&l&6#" . ($i + 1) . ' &r&b' . $targetOffline->getName() . '&f - &e' . $targetOffline->getWins();
            }

            $this->setNameTag(TextFormat::colorize($text));
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);
        }
    }
}