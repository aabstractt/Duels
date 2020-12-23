<?php

namespace duels\arena;

class Kit {

    /** @var string */
    private $name;
    /** @var array */
    private $data;

    /**
     * Kit constructor.
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data) {
        $this->name = $name;

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }
}