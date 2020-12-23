<?php

declare(strict_types=1);

namespace duels\arena;

class Queue {

    /** @var Kit */
    private $kit;
    /** @var bool */
    private $isPremium;

    /**
     * Queue constructor.
     * @param Kit $kit
     * @param bool $isPremium
     */
    public function __construct(Kit $kit, bool $isPremium) {
        $this->kit = $kit;

        $this->isPremium = $isPremium;
    }

    /**
     * @return Kit
     */
    public function getKit(): Kit {
        return $this->kit;
    }

    /**
     * @return bool
     */
    public function isPremium(): bool {
        return $this->isPremium;
    }
}