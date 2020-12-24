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

        if (empty($args[1]) && $sender instanceof Player) {
            $target = $sender;
        }

        if(!(empty($args[1]) && ($args[0] == 'enable' || $args[0] == 'disable')) || isset($args[1])) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <player|empty> <enable|disable>');

            return;
        }

        $permission = $this->getPermission();

        if ($permission == null) return;

        if (isset($args[0]) && $sender->hasPermission($permission)) {
            $target = Server::getInstance()->getPlayer($args[0]);
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