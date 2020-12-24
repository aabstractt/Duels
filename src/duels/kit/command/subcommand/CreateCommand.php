<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\kit\Kit;
use duels\utils\ItemUtils;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class CreateCommand extends PlayerSubCommand {

    /**
     * @param Player $player
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (empty($args)) {
            $player->sendMessage(TextFormat::RED . 'Usage: /kit ' . $this->getName() . ' <name>');

            return;
        }

        $data = [];

        foreach ($player->getArmorInventory()->getContents() as $slot => $content) {
            $data['armor'][$slot] = ItemUtils::itemToString($content);
        }

        foreach ($player->getInventory()->getContents() as $slot => $content) {
            $data['inventory'][$slot] = ItemUtils::itemToString($content);
        }

        (new Kit($args[0], $data))->handleUpdate();

        $player->sendMessage(TextFormat::GREEN . sprintf('Kit %s created', $args[0]));
    }
}