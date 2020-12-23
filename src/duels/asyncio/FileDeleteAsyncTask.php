<?php

declare(strict_types=1);

namespace duels\asyncio;

use pocketmine\Server;

class FileDeleteAsyncTask extends FileOperationTask {

    public function onRun() {
        parent::onRun();

        $this->success = self::recurse_delete($this->source);
    }

    /**
     * @param string $src
     * @return bool
     */
    public static function recurse_delete(string $src): bool {
        if (!is_dir($src)) {
            return false;
        }

        if (substr($src, strlen($src) - 1, 1) != '/') {
            $src .= '/';
        }

        $files = glob($src . '*', GLOB_MARK);

        if (!$files) {
            return false;
        }
        
        foreach ($files as $file) {
            if (is_dir($file)) {
                self::recurse_delete($file);
            } else {
                @unlink($file);
            }
        }

        @rmdir($src);

        return true;
    }

    /**
     * @param Server $server
     */
    protected function onSuccess(Server $server): void {
        if ($this->success) {
            $server->getLogger()->debug('Deleted file ' . $this->source);
        } else {
            $server->getLogger()->error('Unable to delete file ' . $this->source);
        }
    }
}