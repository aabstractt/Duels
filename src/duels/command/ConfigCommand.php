<?php

declare(strict_types=1);

namespace duels\command;

use duels\api\Command;
use duels\command\subcommand\CreateCommand;
use duels\command\subcommand\DuelsCommand;
use duels\command\subcommand\LobbyItemsCommand;
use duels\command\subcommand\SpawnCommand;
use duels\command\subcommand\TopCommand;

class ConfigCommand extends Command {

    /**
     * ConfigCommand constructor.
     */
    public function __construct() {
        parent::__construct('config', 'Config a duel arena', '/config help');

        $this->addCommand(
            new CreateCommand('create', 'config.command.create'),
            new SpawnCommand('spawn', 'Set a spawn'),
            new LobbyItemsCommand('lobbyitems', 'config.command.lobbyitems'),
            new TopCommand('top', 'config.command.top'),
            new DuelsCommand('duels')
        );
    }
}