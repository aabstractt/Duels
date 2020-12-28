<?php

declare(strict_types=1);

namespace duels\queue;

use duels\duel\DuelCommand;
use duels\Duels;
use duels\kit\Kit;
use duels\queue\command\QueueCommand;
use duels\session\Session;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;

class QueueFactory {

    /** @var array<string, Queue> */
    private $queueRanked = [];
    /** @var array<string, Queue> */
    private $queueUnranked = [];

    /**
     * QueueFactory constructor.
     * @noinspection PhpUnusedParameterInspection
     */
    public function __construct() {
        Server::getInstance()->getCommandMap()->register(QueueCommand::class, new QueueCommand());

        Server::getInstance()->getCommandMap()->register(DuelCommand::class, new DuelCommand());

        Duels::getInstance()->getScheduler()->scheduleRepeatingTask(new ClosureTask(function (int $currentTick): void {
            $this->handleQueue();
        }), 20);
    }

    /**
     * @param Kit $kit
     */
    public function createQueue(Kit $kit): void {
        $this->queueUnranked[strtolower($kit->getName())] = new Queue($kit, false);

        if (!Duels::isQueuePremiumEnabled()) return;

        $this->queueRanked[strtolower($kit->getName())] = new Queue($kit);
    }

    /**
     * @param Kit $kit
     */
    public function removeQueue(Kit $kit): void {
        if (empty($this->queueUnranked[strtolower($kit->getName())])) return;

        unset($this->queueUnranked[strtolower($kit->getName())]);

        if (empty($this->queueRanked[strtolower($kit->getName())])) return;

        unset($this->queueRanked[strtolower($kit->getName())]);
    }

    /**
     * @return Queue[]
     */
    public function getQueuesUnranked(): array {
        return $this->queueUnranked;
    }

    /**
     * @return Queue[]
     */
    public function getQueuesRanked(): array {
        return $this->queueRanked;
    }

    /**
     * @param Kit $kit
     * @param bool $isPremium
     * @return Queue
     */
    public function getQueueByKit(Kit $kit, bool $isPremium): Queue {
        return $this->getQueueByKitName($kit->getName(), $isPremium);
    }

    /**
     * @param string $kitName
     * @param bool $isPremium
     * @return Queue
     */
    public function getQueueByKitName(string $kitName, bool $isPremium): Queue {
        if ($isPremium && Duels::isQueuePremiumEnabled()) {
            return $this->queueRanked[strtolower($kitName)];
        }

        return $this->queueUnranked[strtolower($kitName)];
    }

    /**
     * @param Session $session
     */
    public function removeSessionFromQueue(Session $session): void {
        foreach ($this->queueUnranked as $queue) {
            if (!$queue->hasSession($session)) continue;

            $queue->removeSession($session);
        }

        foreach ($this->queueRanked as $queue) {
            if (!$queue->hasSession($session)) continue;

            $queue->removeSession($session);
        }
    }

    /**
     * @param Session $session
     * @return Queue|null
     */
    public function getSessionQueue(Session $session): ?Queue {
        foreach ($this->queueUnranked as $queue) {
            if (!$queue->hasSession($session)) continue;

            return $queue;
        }

        foreach ($this->queueRanked as $queue) {
            if (!$queue->hasSession($session)) continue;

            return $queue;
        }

        return null;
    }

    protected function handleQueue(): void {
        foreach ($this->queueUnranked as $queue) {
            $queue->update();
        }

        foreach ($this->queueRanked as $queue) {
            $queue->update();
        }
    }
}