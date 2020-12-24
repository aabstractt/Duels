<?php

namespace duels\kit;

use duels\Duels;
use duels\session\Session;

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

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    public function handleUpdate(): void {
        Duels::getKitFactory()->createKit($this);
    }

    public function giveKit(Session $session): void {

    }
}