<?php

declare(strict_types=1);

namespace duels\task;

use pocketmine\scheduler\Task;

abstract class GameTask extends Task {

    /** @var string */
    private $taskName;

    /**
     * GameTask constructor.
     * @param string $taskName
     */
    public function __construct(string $taskName) {
        $this->taskName = $taskName;
    }

    /**
     * @return string
     */
    public function getTaskName(): string {
        return $this->taskName;
    }

    /**
     * Actions to execute when run
     *
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick): void {
        if (!$this->beforeRun()) {
            $this->cancel();

            return;
        }

        $this->run();
    }

    /**
     * Action executed when the task run
     */
    public abstract function run(): void;

    /**
     * @return bool
     */
    public function beforeRun(): bool {
        return $this->getHandler() != null;
    }

    public function cancel(): void {
        TaskScheduler::cancelTask($this->getTaskName());
    }
}