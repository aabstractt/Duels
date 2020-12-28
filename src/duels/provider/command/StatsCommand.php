<?php

declare(strict_types=1);

namespace duels\provider\command;

use duels\Duels;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat;

class StatsCommand extends Command {

    /**
     * StatsCommand constructor.
     */
    public function __construct() {
        parent::__construct('stats', 'View stats a player', '/stats <player>');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @throws \Exception
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (empty($args[0])) {
            throw new InvalidCommandSyntaxException();
        }

        $targetOffline = Duels::getInstance()->getProvider()->getTargetOffline($args[0]);

        if ($targetOffline == null) {
            $sender->sendMessage(TextFormat::RED . 'Player not found');

            return;
        }

        $sender->sendMessage(TextFormat::colorize(str_replace(['{0}', '{1}', '{2}'], [$targetOffline->getName(), $targetOffline->getWins(), $targetOffline->getLosses()], Duels::getInstance()->getConfig()->get('message-stats-players', ''))));
    }
}