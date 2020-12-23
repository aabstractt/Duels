<?php

declare(strict_types=1);

namespace duels\task;

use duels\Duels;

class TaskScheduler {

    /** @var int[] */
    private static $tasks = [];

    /**
     * @param GameTask $task
     * @param int $ticks
     */
    public static function scheduleRepeatingTask(GameTask $task, int $ticks = 20): void {
        self::addTask($task->getTaskName(), $task->getTaskId());

        Duels::getInstance()->getScheduler()->scheduleRepeatingTask($task, $ticks);
    }

    /**
     * @param GameTask $task
     * @param int $ticks
     * @param int $period
     */
    public static function scheduleDelayedRepeatingTask(GameTask $task, int $ticks = 20, int $period = 20): void {
        Duels::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($task, $ticks, $period);
    }

    /**
     * @param string $taskName
     * @param int $taskId
     */
    public static function addTask(string $taskName, int $taskId): void {
        self::$tasks[$taskName] = $taskId;
    }

    /**
     * @param string $taskName
     */
    public static function cancelTask(string $taskName): void {
        $taskId = self::$tasks[$taskName] ?? null;

        if ($taskId == null) {
            return;
        }

        unset(self::$tasks[$taskName]);

        Duels::getInstance()->getScheduler()->cancelTask($taskId);
    }
}