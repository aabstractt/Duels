<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\command\utils\InvalidCommandSyntaxException;

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

    private function handleForm(Session $session): void {

    }
}