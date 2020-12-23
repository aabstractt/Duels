<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Kit;
use duels\arena\Queue;

class QueueFactory {

    /** @var array<string, Queue> */
    private $queue = [];

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
}