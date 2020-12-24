<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\SubCommand;
use duels\kit\Kit;
use duels\utils\ItemUtils;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CreateCommand extends SubCommand {

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function run(CommandSender $sender, array $args): void {
        if (empty($args)) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /kit ' . $this->getName() . ' <name>');

            return;
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        $data = [];

        foreach ($sender->getArmorInventory()->getContents() as $slot => $content) {
            $data['armor'][$slot] = ItemUtils::itemToString($content);
        }

        foreach ($sender->getInventory()->getContents() as $slot => $content) {
            $data['inventory'][$slot] = ItemUtils::itemToString($content);
        }

        (new Kit($args[0], $data))->handleUpdate();

        $sender->sendMessage(TextFormat::GREEN . sprintf('Kit %s created', $args[0]));
    }
}