<?php

namespace duels\kit\listener;

use duels\Duels;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\Listener;
use pocketmine\Player;

class EntityListener implements Listener {

    /**
     * @param EntityDamageEvent $ev
     *
     * @priority HIGHEST
     */
    public function onEntityDamageEvent(EntityDamageEvent $ev): void {
        $entity = $ev->getEntity();

        if (!$entity instanceof Player) return;

        $session = Duels::getSessionFactory()->getSessionPlayerNullable($entity);

        if ($session == null) return;

        if ($session->inArena()) return;

        $ffa = Duels::getKitFactory()->getFFAByWorld($entity->getLevelNonNull());

        if ($ffa == null) return;

        if ($ev instanceof EntityDamageByEntityEvent) {
            $target = $ev->getDamager();

            if (!$target instanceof Player) return;

            $targetSession = Duels::getSessionFactory()->getSessionPlayer($target);

            $session->attack($targetSession);
        }

        if (($entity->getHealth() - $ev->getFinalDamage()) / 2 > 0) return;

        $ffa->handlePlayerDeath($session, $session->getLastKiller());

        $ev->setCancelled();
    }
}