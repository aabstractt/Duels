<?php

declare(strict_types=1);

namespace duels\arena\task;

use duels\arena\Arena;
use duels\Duels;
use duels\task\GameTask;

abstract class GameUpdateTask extends GameTask {

    /** @var Arena */
    protected $arena;
    /** @var int */
    private $taskHandlerId;

    /**
     * GameUpdateTask constructor.
     * @param string $taskName
     * @param Arena $arena
     */
    public function __construct(string $taskName, Arena $arena) {
        parent::__construct($taskName);

        $this->arena = $arena;

        $this->taskHandlerId = $arena->getId();
    }

    /**
     * @return bool
     */
    public function beforeRun(): bool {
        $parent = parent::beforeRun();

        if (!$parent) return false;

        if ($this->arena->getWorld() == null) {
            foreach ($this->arena->getAllPlayers() as $player) {
                $player->remove(true);
            }

            Duels::getArenaFactory()->removeArena($this->arena->getId());

            return false;
        }

        return true;
    }

    public function cancel(): void {
        $this->arena->cancelTask($this->getTaskName());
    }
}