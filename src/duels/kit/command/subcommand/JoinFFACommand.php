<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\utils\TextFormat;

class JoinFFACommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (empty($args[0])) {
            $this->handleForm($session);

            return;
        }

        $ffa = Duels::getKitFactory()->getFFA($args[0]);

        if ($ffa == null) {
            $session->sendMessage('&cFFA Kit not found');

            return;
        }

        $ffa->join($session);
    }

    /**
     * @param Session $session
     */
    private function handleForm(Session $session): void {
        $data = [
            'type' => 'form',
            'title' => TextFormat::BLUE . TextFormat::BOLD . 'Map selection',
            'content' => '',
            'buttons' => []
        ];

        $placeHolders = Duels::getInstance()->getConfig()->get('placeHolders', []);

        $fkits = Duels::getKitFactory()->getKitsFFA();

        foreach ($fkits as $ffa) {
            $text = $placeHolders[$ffa->getKit()->getName()] ?? '';

            $data['buttons'][] = TextFormat::colorize(str_replace('{0}', (string)count($ffa->getWorld()->getPlayers()), $text));
        }

        $session->sendForm(function (Session $session, ?int $data) use($fkits): void {
            if ($data === null) return;

            $ffa = array_values($fkits)[$data] ?? null;

            if ($ffa == null) return;

            $ffa->join($session);
        }, $data);
    }
}