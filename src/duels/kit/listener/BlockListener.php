<?php

declare(strict_types=1);

namespace duels\kit\listener;

use duels\Duels;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;

class BlockListener implements Listener {

    /**
     * @param BlockBreakEvent $ev
     *
     * @priority MONITOR
     */
    public function onBlockBreakEvent(BlockBreakEvent $ev): void {
        $player = $ev->getPlayer();

        $session = Duels::getSessionFactory()->getSessionPlayer($player);

        if (!$session->inFFA()) return;

        $ev->setCancelled();
    }

    /**
     * @param BlockPlaceEvent $ev
     *
     * @priority MONITOR
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $ev): void {
        $player = $ev->getPlayer();

        $session = Duels::getSessionFactory()->getSessionPlayer($player);

        if (!$session->inFFA()) return;

        $ev->setCancelled();
    }
}