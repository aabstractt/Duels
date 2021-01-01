<?php

declare(strict_types=1);

namespace duels\kit\command;

use duels\api\Command;
use duels\kit\command\subcommand\JoinFFACommand;

class FFACommand extends Command {

    /**
     * FFACommand constructor.
     */
    public function __construct() {
        parent::__construct('ffa', 'FFA Command');

        $this->addCommand(new JoinFFACommand('join'));
    }
}