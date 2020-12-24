<?php

declare(strict_types=1);

namespace duels\arena\task;

use duels\Duels;

class GameFinishUpdateTask extends GameUpdateTask {

    /** @var int */
    protected $timePassed = 0;

    public function run(): void {
        $arena = $this->arena;

        if ($arena == null) {
            return;
        }

        if (!$arena->isFinishing()) {
            return;
        }

        if ($this->timePassed == 10) {
            foreach ($arena->getAllPlayers() as $player) {
                $player->remove(true);
            }
        }

        if ($this->timePassed == 13) {
            Duels::getInstance()->removeWorld($arena->getWorldName());

            $this->cancel();

            Duels::getArenaFactory()->removeArena($arena->getId());

            return;
        }

        $this->timePassed++;
    }
}