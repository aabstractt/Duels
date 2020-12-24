<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SpawnCommand extends PlayerSubCommand {

    /**
     * @param Player $player
     * @param array $args
     */
    public function onRun(Player $player, array $args): void {
        if (empty($args[0])) {
            $player->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <slot>');

            return;
        }

        if ($player->getLevelNonNull() === Server::getInstance()->getDefaultLevel()) {
            $player->sendMessage(TextFormat::RED . 'You can\'t setup maps in the lobby.');

            return;
        }

        $level = Duels::getLevelFactory()->getLevel($player->getLevelNonNull()->getFolderName());

        if ($level == null) {
            $player->sendMessage(TextFormat::RED . 'This arena doesn\'t exist.');

            return;
        }

        if (!is_numeric(($slot = (int)$args[1])) || $slot < 1 || $slot > $level->getMaxSlots()) {
            $player->sendMessage(TextFormat::RED . 'You must specify a slot between 1-' . $level->getMaxSlots() . '.');

            return;
        }

        $level->addSlotPosition($slot, ($loc = $player->getLocation()));

        $player->sendMessage(TextFormat::BLUE . 'Spawn ' . $slot . ' set to §6X:§b ' . $loc->getX() . ' §6Y:§b ' . $loc->getY() . ' §6Z:§b ' . $loc->getZ() . ' §6Yaw:§b ' . $loc->getYaw() . ' §6Pitch:§b ' . $loc->getPitch());

        $level->handleUpdate();
    }
}