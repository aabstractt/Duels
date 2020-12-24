<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class SpawnCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (empty($args[0])) {
            $session->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <slot>');

            return;
        }

        if ($session->getLevelNonNull() === Server::getInstance()->getDefaultLevel()) {
            $session->sendMessage(TextFormat::RED . 'You can\'t setup maps in the lobby.');

            return;
        }

        $level = Duels::getLevelFactory()->getLevel($session->getLevelNonNull()->getFolderName());

        if ($level == null) {
            $session->sendMessage(TextFormat::RED . 'This arena doesn\'t exist.');

            return;
        }

        if (!is_numeric(($slot = (int)$args[0])) || $slot < 1 || $slot > $level->getMaxSlots()) {
            $session->sendMessage(TextFormat::RED . 'You must specify a slot between 1-' . $level->getMaxSlots() . '.');

            return;
        }

        $level->addSlotPosition($slot, ($loc = $session->getGeneralPlayer()->getLocation()));

        $session->sendMessage(TextFormat::BLUE . 'Spawn ' . $slot . ' set to §6X:§b ' . $loc->getX() . ' §6Y:§b ' . $loc->getY() . ' §6Z:§b ' . $loc->getZ() . ' §6Yaw:§b ' . $loc->getYaw() . ' §6Pitch:§b ' . $loc->getPitch());

        $level->handleUpdate();
    }
}