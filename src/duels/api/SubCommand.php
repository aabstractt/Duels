<?php

declare(strict_types=1);

namespace duels\api;

use pocketmine\command\CommandSender;

abstract class SubCommand {

    /** @var string */
    private $name;
    /** @var string */
    private $permission;

    /**
     * SubCommand constructor.
     * @param string $name
     * @param string $permission
     */
    public function __construct(string $name, string $permission) {
        $this->name = $name;

        $this->permission = $permission;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getPermission(): string {
        return $this->permission;
    }

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public abstract function run(CommandSender $sender, array $args): void;
}