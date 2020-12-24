<?php

declare(strict_types=1);

namespace duels\arena\task;

class GameMatchUpdateTask extends GameUpdateTask {

    /** @var int */
    protected $timePassed = 0;

    /**
     * Action executed when the task run
     */
    public function run(): void {
        $arena = $this->arena;

        if ($arena == null) {
            return;
        }

        if (!$arena->isStarted()) {
            $this->cancel();

            return;
        }

        $players = $arena->getSessions();

        if (count($players) <= 1) {
            $this->cancel();

            $arena->finish($players);

            return;
        }

        $arena->getScoreboard()->setLine(10, '&fTime left: &d' . date('i:s', ((5*60) - $this->timePassed)));

        $this->timePassed++;
    }

    /**
     * @return bool
     */
    public function beforeRun(): bool {
        $parent = parent::beforeRun();

        if (!$parent) return false;

        if (!$this->arena->isStarted()) {
            return false;
        }

        return true;
    }
}