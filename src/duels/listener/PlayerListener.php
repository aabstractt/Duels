<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;

class PlayerListener implements Listener {

    /**
     * @param PlayerJoinEvent $ev
     *
     * @priority MONITOR
     * @ignoreCancelled true
     */
    public function onPlayerJoinEvent(PlayerJoinEvent $ev): void {
        $player = $ev->getPlayer();

        Duels::getSessionFactory()->createSession($player->getName());
    }

    /**
     * @param PlayerExhaustEvent $ev
     *
     * @priority MONITOR
     */
    public function onPlayerExhaustEvent(PlayerExhaustEvent $ev): void {
        $player = $ev->getPlayer();

        if (!$player instanceof Player) return;

        $session = Duels::getSessionFactory()->getSessionPlayerNullable($player->getName());

        if ($session == null) return;

        if ($session->isEnergized()) $ev->setCancelled();
    }

    /**
     * @param PlayerQuitEvent $ev
     *
     * @priority NORMAL
     */
    public function onPlayerQuitEvent(PlayerQuitEvent $ev): void {
        $player = $ev->getPlayer();

        Duels::getSessionFactory()->removeSession($player);
    }
}