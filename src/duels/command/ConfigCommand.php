<?php

declare(strict_types=1);

namespace duels\command;

use duels\api\Command;
use duels\command\subcommand\CreateCommand;

class ConfigCommand extends Command {

    /**
     * ConfigCommand constructor.
     */
    public function __construct() {
        parent::__construct('config', 'Config a duel arena');

        $this->addCommand(new CreateCommand('create', 'config.command.create'));
    }
}