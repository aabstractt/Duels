<?php

declare(strict_types=1);

namespace duels\kit\command;

use duels\api\Command;
use duels\kit\command\subcommand\CreateCommand;

class KitCommand extends Command {

    public function __construct() {
        parent::__construct('kit', 'Kit Command');

        $this->addCommand(new CreateCommand('create', 'kit.command.create'));
    }
}