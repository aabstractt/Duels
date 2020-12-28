<?php

declare(strict_types=1);

namespace duels\queue\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\utils\TextFormat;

class JoinCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (!isset($args[0], $args[1])) {
            $session->sendMessage(TextFormat::RED . 'Usage: /queue ' . $this->getName() . ' <kit> <ranked/unranked>');

            return;
        }

        if ($args[1] != 'ranked' && $args[1] != 'unranked') {
            $session->sendMessage(TextFormat::RED . 'Usage: /queue ' . $this->getName() . ' <kit> <ranked/unranked>');

            return;
        }

        $kit = Duels::getKitFactory()->getKit($args[0]);

        if ($kit == null) {
            $session->sendMessage(TextFormat::RED . 'Kit not found');

            return;
        }

        $queue = Duels::getQueueFactory()->getQueueByKit($kit, $args[1] == 'ranked');

        if ($queue->isPremium()) {
            if (!$session->getGeneralPlayer()->hasPermission($this->getPermission() . '.premium')) {
                $session->sendMessage(TextFormat::RED . 'You don\'t have permission to join this queue');

                return;
            }
        }

        if ($queue->addSession($session)) {
            $session->sendMessage(TextFormat::GREEN . 'You now are in queue for ' . $kit->getName() . '.');

            return;
        }

        $session->sendMessage(TextFormat::RED . 'You already are in this queue');
    }
}