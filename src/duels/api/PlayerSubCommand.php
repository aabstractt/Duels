<?php

declare(strict_types=1);

namespace duels\api;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class PlayerSubCommand extends SubCommand {

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function run(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }
    }

    /**
     * @param Player $player
     * @param array $args
     */
    public abstract function onRun(Player $player, array $args): void;
}