<?php

declare(strict_types=1);

namespace duels\queue\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\utils\TextFormat;

class LeaveCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        $queue = Duels::getQueueFactory()->getSessionQueue($session);

        if ($queue == null) {
            $session->sendMessage(TextFormat::RED . 'You are not in any queue');

            return;
        }

        $queue->removeSession($session);

        Duels::getDefaultScoreboard()->removePlayer($session);
        Duels::getDefaultScoreboard()->addPlayer($session);

        $session->updateScoreboard();

        $session->sendMessage(TextFormat::RED . 'You left the queue');
    }
}