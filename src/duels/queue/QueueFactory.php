<?php

declare(strict_types=1);

namespace duels\queue;

use duels\Duels;
use duels\kit\Kit;
use duels\queue\command\QueueCommand;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class QueueFactory {

    /** @var array<string, Queue> */
    private $queue = [];

    /**
     * QueueFactory constructor.
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct() {
        Server::getInstance()->getCommandMap()->register(QueueCommand::class, new QueueCommand());

        Duels::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            $this->handleQueue();
        }), 20);
    }

    /**
     * @param Kit $kit
     * @return Queue|null
     */
    public function getQueueByKit(Kit $kit): ?Queue {
        return $this->getQueueByKitName($kit->getName());
    }

    /**
     * @param string $kitName
     * @return Queue|null
     */
    public function getQueueByKitName(string $kitName): ?Queue {
        return $this->queue[strtolower($kitName)] ?? null;
    }

    protected function handleQueue(): void {
        foreach ($this->queue as $queue) {
            $queue->update();
        }
    }
}