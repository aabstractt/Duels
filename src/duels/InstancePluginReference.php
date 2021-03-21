<?php

declare(strict_types=1);

namespace duels;

trait InstancePluginReference {

    /** @var self|null */
    private static $instance = null;

    /**
     * @return self
     */
    public static function getInstance(): self {
        if (self::$instance == null) {
            self::$instance = new self();
        }

        return self::$instance;
    }
}