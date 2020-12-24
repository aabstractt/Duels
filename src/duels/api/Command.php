<?php

declare(strict_types=1);

namespace duels\api;

use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;

class Command extends \pocketmine\command\Command {

    /** @var array<string, SubCommand> */
    private $commands = [];

    /**
     * @param SubCommand ...$commands
     */
    protected function addCommand(SubCommand ...$commands): void {
        foreach ($commands as $command) {
            $this->commands[strtolower($command->getName())] = $command;
        }
    }

    /**
     * @param string $name
     * @return SubCommand|null
     */
    protected function getCommand(string $name): ?SubCommand {
        return $this->commands[strtolower($name)] ?? null;
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (count($this->commands) != 0 && count($args) == 0) {
            throw new InvalidCommandSyntaxException();
        }

        $name = array_shift($args);

        if ($name == null) {
            throw new InvalidCommandSyntaxException();
        }

        $command = $this->getCommand($name);

        if ($command == null) {
            throw new InvalidCommandSyntaxException();
        }

        if (($permission = $command->getPermission()) != null && !$sender->hasPermission($permission)) {
            $sender->sendMessage(TextFormat::RED . 'You don\'t have permissions to use this command');

            return;
        }

        $command->run($sender, $args);
    }
}