<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerDropItemEvent;

class InventoryListener implements Listener {

    /**
     * @param PlayerDropItemEvent $ev
     *
     * @priority NORMAL
     * @ignoreCancelled true
     */
    public function onPlayerDropItemEvent(PlayerDropItemEvent $ev): void {
        $player = $ev->getPlayer();

        $session = Duels::getSessionFactory()->getSessionPlayer($player);

        $arena = $session->getArena();

        if ($arena == null) return;

        if ($arena->isStarted() || ($arena->isStarted() && !$session->isSpectator())) return;

        $ev->setCancelled();
    }

    /**
     * @param InventoryTransactionEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onInventoryTransaction(InventoryTransactionEvent $ev): void {
        $player = $ev->getTransaction()->getSource();

        $session = Duels::getSessionFactory()->getSessionPlayer($player);

        if ($player->isOp()) return;

        $arena = $session->getArena();

        if ($arena == null) return;

        if ($arena->isStarted() || ($arena->isStarted() && !$session->isSpectator())) return;

        $ev->setCancelled();
    }
}