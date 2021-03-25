<?php

namespace duels\command;

use duels\Duels;
use duels\session\SessionException;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class LeaveCommand extends Command {

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        try {
            $session = Duels::getSessionFactory()->getSessionPlayer($sender);

            $session->teleport(Duels::getDefaultLevelNonNull()->getSpawnLocation());

            $session->setDefaultLobbyAttributes(true);

            $session->sendMessage(TextFormat::GREEN . 'Sending you to the Lobby.');
        } catch (SessionException $exception) {
            $sender->kick($exception->getMessage());
        }
    }
}