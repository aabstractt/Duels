<?php

declare(strict_types=1);

namespace duels\listener;

use duels\Duels;
use pocketmine\event\entity\EntityLevelChangeEvent;
use pocketmine\event\Listener;
use pocketmine\Player as pocketPlayer;

class EntityListener implements Listener {

    /**
     * @param EntityLevelChangeEvent $ev
     *
     * @priority HIGHEST
     */
    public function onEntityLevelChangeEvent(EntityLevelChangeEvent $ev): void {
        $entity = $ev->getEntity();

        if (!$entity instanceof pocketPlayer) return;

        $session = Duels::getSessionFactory()->getSessionPlayer($entity);

        if (!$session->inArena()) return;

        if (!$session->isInsideArena($ev->getTarget())) {
            $session->remove(true);
        }
    }
}