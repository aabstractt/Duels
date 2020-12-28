<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use duels\utils\ItemUtils;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;
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

    /**
     * @param PlayerDropItemEvent $ev
     *
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerDropItemEvent(PlayerDropItemEvent $ev): void {
        $item = $ev->getItem();

        $nbt = $item->getCustomBlockData();

        if ($nbt == null) return;

        $nameString = $nbt->getString('Name');

        if ($nameString == null || $nameString == '') return;

        $commandString = $nbt->getString('Command');

        if ($commandString == null) return;

        $ev->setCancelled();
    }

    /**
     * @param InventoryTransactionEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onInventoryTransaction(InventoryTransactionEvent $ev): void {
        foreach ($ev->getTransaction()->getActions() as $action) {
            $nbt = $action->getSourceItem()->getCustomBlockData();

            if ($nbt == null) return;

            $nameString = $nbt->getString('Name');

            if ($nameString == null || $nameString == '') return;

            $commandString = $nbt->getString('Command');

            if ($commandString == null) return;

            $ev->setCancelled();
        }
    }

    /**
     * @param InventoryTransactionEvent $ev
     *
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onInventoryTransaction2(InventoryTransactionEvent $ev): void {
        foreach ($ev->getTransaction()->getActions() as $action) {
            $nbt = $action->getTargetItem()->getCustomBlockData();

            if ($nbt == null) return;

            $nameString = $nbt->getString('Name');

            if ($nameString == null || $nameString == '') return;

            $commandString = $nbt->getString('Command');

            if ($commandString == null) return;

            $ev->setCancelled();
        }
    }
}