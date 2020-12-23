<?php

declare(strict_types=1);

namespace duels\asyncio;

use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

abstract class FileOperationTask extends AsyncTask {

    /** @var string */
    protected $source;
    /** @var float */
    protected $taskTime;
    /** @var bool */
    protected $success = false;

    /**
     * FileOperationTask constructor.
     * @param string $source
     * @param callable|null $callback
     */
    public function __construct(string $source, ?callable $callback = null) {
        $this->source = $source;

        $this->storeLocal($callback);
    }

    /**
     * Actions to execute when run
     *
     * @return void
     */
    public function onRun() {
        $this->taskTime = microtime(true);
    }

    /**
     * @param Server $server
     */
    protected abstract function onSuccess(Server $server): void;

    /**
     * @param Server $server
     */
    public function onCompletion(Server $server) {
        $this->taskTime = microtime(true) - $this->taskTime;

        $this->onSuccess($server);

        if (!$this->isSuccess()) return;

        $callback = $this->fetchLocal();

        if (!is_callable($callback)) return;

        $callback($this->taskTime);
    }

    protected function isSuccess(): bool {
        return $this->success;
    }
}