<?php

declare(strict_types=1);

namespace duels\task;

abstract class TaskHandlerStorage {

    /** @var int */
    protected $id;

    /**
     * TaskHandlerStorage constructor.
     * @param int $taskHandlerId
     */
    public function __construct(int $taskHandlerId) {
        $this->id = $taskHandlerId;
    }

    /**
     * @return int
     */
    public function getId(): int {
        return $this->id;
    }

    /**
     * @param GameTask $task
     * @param int $ticks
     */
    public function scheduleRepeatingTask(GameTask $task, int $ticks = 20): void {
        TaskScheduler::addTask($task->getTaskName() . $this->id, $ticks);

        TaskScheduler::scheduleRepeatingTask($task, $ticks);
    }

    /**
     * @param string $taskName
     */
    public function cancelTask(string $taskName): void {
        TaskScheduler::cancelTask($taskName . $this->id);
    }
}