<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\utils\TextFormat;

class DeleteCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (empty($args)) {
            $session->sendMessage(TextFormat::RED . 'Usage: /kit ' . $this->getName() . ' <kit>');

            return;
        }

        if (!Duels::getKitFactory()->isKit($args[0])) {
            $session->sendMessage(TextFormat::RED . 'Kit not found');

            return;
        }

        Duels::getKitFactory()->removeKit($args[0]);

        $session->sendMessage(TextFormat::GREEN . 'Kit successfully removed.');
    }
}