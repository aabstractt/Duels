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

        $ffa = Duels::getKitFactory()->getFFAByWorld($player->getLevel());

        if ($ffa == null) return;

        if ($ffa->getKit()->canBuild()) return;
		
		if ($player->isCreative()) return;

        $ev->setCancelled();
    }

    /**
     * @param BlockPlaceEvent $ev
     *
     * @priority MONITOR
     */
    public function onBlockPlaceEvent(BlockPlaceEvent $ev): void {
        $player = $ev->getPlayer();

        $ffa = Duels::getKitFactory()->getFFAByWorld($player->getLevel());

        if ($ffa == null) return;

        if ($ffa->getKit()->canBuild()) return;

        if ($player->isCreative()) return;

        $ev->setCancelled();
    }
}