<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Kit;
use duels\arena\Queue;
use pocketmine\scheduler\ClosureTask;

class QueueFactory {

    /** @var array<string, Queue> */
    private $queue = [];

    /**
     * QueueFactory constructor.
     */
    public function __construct() {
        Duels::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            $this->handleQueue();
        }), 20);
    }

    /**
     * @param Kit $kit
     * @return Queue
     */
    public function getQueueByKit(Kit $kit): Queue {
        return $this->getQueueByKitName($kit->getName());
    }

    /**
     * @param string $kitName
     * @return Queue
     */
    public function getQueueByKitName(string $kitName): Queue {
        return $this->queue[strtolower($kitName)];
    }

    public function handleQueue(): void {
        foreach ($this->queue as $queue) {
            $queue->update();
        }
    }
}