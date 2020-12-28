<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\math\GameLocation;
use duels\session\Session;
use pocketmine\utils\TextFormat;

class TopCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        $config = Duels::getInstance()->getConfig();

        $config->set('entity-leaderboard', array_merge(GameLocation::toArray($session->getGeneralPlayer()->asLocation()), ['username' => $session->getName()]));
        $config->save();

        $session->sendMessage(TextFormat::GREEN . 'Successfully created Leaderboard');
    }
}