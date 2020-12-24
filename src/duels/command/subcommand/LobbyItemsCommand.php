<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\SubCommand;
use duels\Duels;
use duels\session\SessionException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class LobbyItemsCommand extends SubCommand {

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function run(CommandSender $sender, array $args): void {
        /** @var Player $target */
        $target = null;

        if ((isset($args[0]) && isset($args[1])) && (($permission = $this->getPermission()) !== null && $sender->hasPermission($permission))) {
            if ($args[1] != 'enable' && $args[1] == 'disable') {
                $sender->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <player> <enable|disable>');

                return;
            }

            $target = Server::getInstance()->getPlayer($args[0]);
        }

        if ((isset($args[0]) && empty($args[1])) && $sender instanceof Player) {
            if ($args[1] != 'enable' && $args[1] == 'disable') {
                $sender->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <player> <enable|disable>');

                return;
            }

            $target = $sender;
        }

        if ($target == null) {
            $sender->sendMessage(TextFormat::RED . 'Player not found');

            return;
        }

        try {
            $session = Duels::getSessionFactory()->getSessionPlayer($target);

            $session->setLobbyItemsEnabled($args[(isset($args[1]) ? 1 : 0)] == 'enable');

            if (strtolower($session->getName()) != $sender->getName()) {
                $sender->sendMessage(TextFormat::GREEN . 'You have disabled lobby items to ' . $session->getName());
            } else {
                $sender->sendMessage(TextFormat::GREEN . 'You have disabled the lobby items.');
            }

            if ($session->getLevelNonNull() !== Server::getInstance()->getDefaultLevel()) return;

            $session->setDefaultLobbyAttributes();

        } catch (SessionException $e) {
            $sender->sendMessage($e->getMessage());
        }
    }
}