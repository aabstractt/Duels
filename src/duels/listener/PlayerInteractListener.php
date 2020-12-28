<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use duels\utils\ItemUtils;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\Server;

class PlayerInteractListener implements Listener {

    /**
     * @param PlayerInteractEvent $ev
     *
     * @priority HIGHEST
     */
    public function onPlayerInteractEvent(PlayerInteractEvent $ev): void {
        $player = $ev->getPlayer();

        $session = Duels::getSessionFactory()->getSessionPlayerNullable($player->getName());

        if ($session == null) return;

        $item = $ev->getItem();

        $nbt = $item->getCustomBlockData();

        if ($nbt == null) return;

        $nameString = $nbt->getString('Name');

        if ($nameString == null || $nameString == '') return;

        $commandString = $nbt->getString('Command');

        if ($commandString == null) return;

        foreach (ItemUtils::getLobbyItems() as $lobbyItem) {
            $nbt = $lobbyItem->getCustomBlockData();

            if ($nbt == null) continue;

            $newNameString = $nbt->getString('Name');

            if ($newNameString == null || $newNameString == '') continue;

            if ($newNameString !== $nameString) continue;

            Server::getInstance()->dispatchCommand($player, $commandString);

            break;
        }

        $arena = $session->getArena();

        if ($arena == null) return;

        if ($arena->isStarted()) {
            foreach (ItemUtils::getSpectatorItems() as $lobbyItem) {
                $nbt = $lobbyItem->getCustomBlockData();

                if ($nbt == null) continue;

                $newNameString = $nbt->getString('Name');

                if ($newNameString == null || $newNameString == '') continue;

                if ($newNameString !== $nameString) continue;

                Server::getInstance()->dispatchCommand($player, $commandString);

                break;
            }

            return;
        }
    }
}