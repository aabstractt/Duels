<?php

declare(strict_types=1);

namespace duels\utils;

use pocketmine\entity\Human;

class LeaderboardEntity extends Human {

    /**
     * @return string
     */
    public function getName(): string {
        return 'Leaderboard';
    }
}