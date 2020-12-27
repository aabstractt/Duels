<?php

declare(strict_types=1);

namespace duels\queue\command;

use duels\api\Command;
use duels\queue\command\subcommand\JoinCommand;
use duels\queue\command\subcommand\LeaveCommand;
use duels\queue\command\subcommand\ListCommand;

class QueueCommand extends Command {

    /**
     * QueueCommand constructor.
     */
    public function __construct() {
        parent::__construct('queue', 'Queue command', '/queue help');

        $this->addCommand(
            new JoinCommand('join'),
            new LeaveCommand('leave'),
            new ListCommand('list')
        );
    }
}