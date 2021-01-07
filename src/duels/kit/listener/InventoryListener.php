<?php

declare(strict_types=1);

namespace duels\kit\listener;

use duels\Duels;
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

        if (!$session->inFFA()) return;

        if ($player->isCreative()) return;

        $ev->setCancelled();
    }
}