<?php

declare(strict_types=1);

namespace duels\kit\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\kit\Kit;
use duels\session\Session;
use duels\utils\ItemUtils;
use pocketmine\utils\TextFormat;

class CreateCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (!isset($args[0], $args[1])) {
            $session->sendMessage(TextFormat::RED . 'Usage: /kit ' . $this->getName() . ' <name> <placeholder>');

            return;
        }

        $name = array_shift($args[0]);

        $data = [];

        $instance = $session->getGeneralPlayer();

        foreach ($instance->getArmorInventory()->getContents() as $slot => $content) {
            $data['armor'][$slot] = ItemUtils::itemToString($content);
        }

        foreach ($instance->getInventory()->getContents() as $slot => $content) {
            $data['inventory'][$slot] = ItemUtils::itemToString($content);
        }

        (new Kit($name, $data))->handleUpdate();

        Duels::getInstance()->addPlaceHolder($name, implode(' ', $args));

        $session->sendMessage(TextFormat::GREEN . sprintf('Kit %s created', $args[0]));
    }
}