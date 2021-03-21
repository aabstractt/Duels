<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\session\Session;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DuelsCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        $data = [
            'type' => 'form',
            'title' => TextFormat::BLUE . TextFormat::BOLD . 'Play Duels',
            'content' => 'Select which mode you would like to play.',
            'buttons' => [
                ['text' => 'Ranked Duels'],
                ['text' => 'Unranked Duels'],
                ['text' => 'Request Duels'],
                ['text' => 'Spec Duels']
            ]
        ];

        $session->sendForm(function (Session $session, ?int $data): void {
            if ($data === null) return;

            if ($data <= 1) {
                Server::getInstance()->dispatchCommand($session->getGeneralPlayer(), 'queue list ' . ($data == 0 ? 'ranked' : 'unranked'));
            } else {
                $session->sendMessage('Soon');
            }
        }, $data);
    }
}