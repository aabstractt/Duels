<?php

declare(strict_types=1);

namespace duels\queue\command;

use duels\api\Command;

class QueueCommand extends Command {

    /**
     * QueueCommand constructor.
     */
    public function __construct() {
        parent::__construct('queue', 'Queue command', '/queue help');
    }
}