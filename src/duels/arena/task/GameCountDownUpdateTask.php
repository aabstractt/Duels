<?php

declare(strict_types=1);

namespace duels\arena\task;

use duels\arena\Arena;

class GameCountDownUpdateTask extends GameUpdateTask {
    
    /** @var int */
    protected $initialCountdown = 15;
    /** @var int */
    private $countdown;

    /**
     * GameCountDownUpdateTask constructor.
     * @param string $taskName
     * @param Arena $arena
     * @param int $initialCountdown
     */
    public function __construct(string $taskName, Arena $arena, $initialCountdown = 30) {
        parent::__construct($taskName, $arena);

        $this->initialCountdown = $initialCountdown;

        $this->countdown = $initialCountdown;
    }

    /**
     * Action executed when the task run
     */
    public function run(): void {
        $arena = $this->arena;

        if ($arena == null) {
            return;
        }

        $joined = count($arena->getSessions());

        if (!$arena->isWaiting()) {
            $this->cancel();

            return;
        }

        if ($joined >= $arena->getLevel()->getMinSlots()) {
            if (in_array($this->countdown, [60, 50, 40, 30, 20, 10]) || $this->countdown <= 5) {
                $arena->broadcastMessage('&eStarting in ' . $this->countdown . ' second' . ($this->countdown == 1 ? '' : 's'));
            }

            if ($this->countdown == 0) {
                $arena->start();

                $this->cancel();

                return;
            }

            $arena->getScoreboard()->setLine(9, '&4Starting: &c' . $this->countdown);

            $this->countdown--;

            return;
        }
        
        if ($this->countdown !== $this->initialCountdown) {
            $arena->broadcastMessage('&cWe don\'t have enough players! Start cancelled.');

            $arena->setStatus(Arena::STATUS_WAITING);

            foreach ($arena->getAllPlayers() as $session) $session->remove(true);

            $this->countdown = $this->initialCountdown;
        }
    }

    /**
     * @return bool
     */
    public function beforeRun(): bool {
        $parent = parent::beforeRun();

        if (!$parent) return false;
        
        if (!$this->arena->isWaiting()) {
            return false;
        }
        
        if (count($this->arena->getSessions()) <= 0) {
            return false;
        }
        
        return true;
    }
}