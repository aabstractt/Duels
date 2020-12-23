<?php

declare(strict_types=1);

namespace duels\asyncio;

use pocketmine\Server;

class FileCopyAsyncTask extends FileOperationTask {

    /** @var string */
    private $destination;

    /**
     * FileCopyAsyncTask constructor.
     * @param string $src
     * @param string $dst
     * @param callable|null $callback
     */
    public function __construct(string $src, string $dst, ?callable $callback = null) {
        parent::__construct($src, $callback);

        $this->destination = $dst;
    }

    public function onRun() {
        parent::onRun();

        $this->success = self::recurse_copy($this->source, $this->destination);
    }

    /**
     * @param Server $server
     */
    protected function onSuccess(Server $server): void {
        if ($this->success) {
            $server->getLogger()->debug(sprintf('Copied file "%s" to "%s"', $this->source, $this->destination));
        } else {
            $server->getLogger()->error(sprintf('Unable to copy file "%s" to "%s"', $this->source, $this->destination));
        }
    }

    /**
     * @param string $src
     * @param string $dst
     * @return bool
     */
    public static function recurse_copy(string $src, string $dst): bool {
        $dir = opendir($src);

        if ($dir === false) {
            return false;
        }

        if (is_dir($dst)) {
            FileDeleteAsyncTask::recurse_delete($dst);
        }

        if (!@mkdir($dst, 0777, true) && !is_dir($dst)) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', $dst));
        }

        while (($file = readdir($dir)) !== false) {
            if ($file != '.' && $file != '..') {
                if (is_dir($src . '/' . $file)) {
                    self::recurse_copy($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }

        closedir($dir);

        return true;
    }
}