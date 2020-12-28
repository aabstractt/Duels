<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityLevelChangeEvent;
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

        $session = Duels::getSessionFactory()->getSessionPlayer($entity);

        if ($session == null) return;

        $arena = $session->getArena();

        if ($arena == null) return;

        if (!$arena->isStarted() || $session->isSpectator()) {
            $ev->setCancelled();

            return;
        }

        if ($ev instanceof EntityDamageByEntityEvent) {
            $target = $ev->getDamager();

            if (!$target instanceof Player) return;

            $targetSession = Duels::getSessionFactory()->getSessionPlayer($target);

            $session->attack($targetSession);
        }

        if (($entity->getHealth() - $ev->getFinalDamage()) / 2 > 0) return;

        $ev->setCancelled();
    }

    /**
     * @param EntityLevelChangeEvent $ev
     *
     * @priority HIGHEST
     */
    public function onEntityLevelChangeEvent(EntityLevelChangeEvent $ev): void {
        $entity = $ev->getEntity();

        if (!$entity instanceof Player) return;

        $session = Duels::getSessionFactory()->getSessionPlayer($entity);

        if (!$session->inArena()) return;

        if (!$session->isInsideArena($ev->getTarget())) {
            $session->remove(true);
        }
    }
}