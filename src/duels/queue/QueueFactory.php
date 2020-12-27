<?php

declare(strict_types=1);

namespace duels\queue;

use duels\Duels;
use duels\kit\Kit;
use duels\queue\command\QueueCommand;
use duels\session\Session;
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
     */
    public function createQueue(Kit $kit): void {
        $this->queue[strtolower($kit->getName())] = new Queue($kit, false);
    }

    /**
     * @return Queue[]
     */
    public function getQueues(): array {
        return $this->queue;
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

    /**
     * @param Session $session
     */
    public function removeSessionFromQueue(Session $session): void {
        foreach ($this->queue as $queue) {
            if (!$queue->hasSession($session)) continue;

            $queue->removeSession($session);
        }
    }

    /**
     * @param Session $session
     * @return Queue|null
     */
    public function getSessionQueue(Session $session): ?Queue {
        foreach ($this->queue as $queue) {
            if (!$queue->hasSession($session)) continue;

            return $queue;
        }

        return null;
    }

    protected function handleQueue(): void {
        foreach ($this->queue as $queue) {
            $queue->update();
        }
    }
}