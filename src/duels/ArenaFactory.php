<?php

declare(strict_types=1);

namespace duels;

use duels\arena\Arena;
use duels\arena\Level;
use duels\kit\Kit;
use duels\session\Session;
use pocketmine\plugin\PluginException;
use pocketmine\Server;

class ArenaFactory {

    /** @var array<int, Arena> */
    protected $arenas = [];
    /** @var int */
    private $gamesPlayed = 1;

    /**
     * @param Session[] $sessions
     * @param bool $isPremium
     * @param Kit $kit
     * @param Level|null $level
     * @return void
     */
    public function createArena(array $sessions, bool $isPremium, Kit $kit, Level $level = null): void {
        if ($level === null) {
            $level = Duels::getLevelFactory()->getRandomLevel($kit);
        }

        try {
            if ($level == null) {
                throw new PluginException('Level not found');
            }

            $arena = Duels::getInstance()->generateNewArena($this->gamesPlayed++, $isPremium, $level);

            $this->arenas[$arena->getId()] = $arena;

            $arena->addSessions($sessions);
        } catch (PluginException $e) {
            Server::getInstance()->getLogger()->logException($e);
        }
    }

    /**
     * @param string $folderName
     * @return Arena[]
     */
    public function getArenas(string $folderName): array {
        /** @var Arena[] $arenas */
        $arenas = [];

        foreach ($this->arenas as $arena) {
            if ($arena->getLevel()->getFolderName() !== $folderName) continue;

            $arenas[$arena->getId()] = $arena;
        }

        return $arenas;
    }

    /**
     * @param Kit $kit
     * @param bool $isPremium
     * @return array<int, Arena>
     */
    public function getKitArenas(Kit $kit, bool $isPremium): array {
        /** @var array<int, Arena> $arenas */
        $arenas = [];

        foreach ($this->arenas as $arena) {
            if (strtolower($arena->getLevel()->getKit()->getName()) !== strtolower($kit->getName())) continue;

            if ($arena->isPremium() != $isPremium) continue;

            $arenas[$arena->getId()] = $arena;
        }

        return $arenas;
    }

    /**
     * @param Kit $kit
     * @param bool $isPremium
     * @return Session[]
     */
    public function getKitSessions(Kit $kit, bool $isPremium): array {
        /** @var array<string, Session> $sessions */
        $sessions = [];

        foreach ($this->getKitArenas($kit, $isPremium) as $arena) {
            $sessions = array_merge($sessions, $arena->getSessions());
        }

        return $sessions;
    }

    /**
     * @param Session $session
     * @return Arena|null
     */
    public function getArena(Session $session): ?Arena {
        foreach ($this->arenas as $arena) {
            if (!$arena->inArenaAsPlayerOrSpectator($session)) continue;

            return $arena;
        }

        return null;
    }

    /**
     * @param int $id
     * @return Arena|null
     */
    public function getArenaId(int $id): ?Arena {
        return $this->arenas[$id] ?? null;
    }

    /**
     * @param int $id
     */
    public function removeArena(int $id): void {
        if (!isset($this->arenas[$id])) return;

        unset($this->arenas[$id]);
    }

    /**
     * @return array<string, Session>
     */
    public function getSessionsPlaying(): array {
        $sessions = [];

        foreach ($this->arenas as $arena) {
            $sessions = array_merge($sessions, $arena->getSessions());
        }

        return $sessions;
    }

    /**
     * @param Session $session
     * @param Session|null $killerSession
     */
    public function handlePlayerDeath(Session $session, ?Session $killerSession): void {
        $arena = $session->getArena();

        if ($arena == null) return;

        $pos = $arena->getLevel()->getSlotPosition($session->getSlot(), $arena->getWorldNonNull());

        $message = '&c' . $session->getName() . '&f was slain';

        if ($killerSession != null) {
            $message .= ' by &c' . $killerSession->getName();

            $pos = $killerSession->getGeneralPlayer()->asPosition();
        }

        $session->getTargetOffline()->increaseLosses();

        $session->setResetPlayerAttributes();

        $session->teleport($pos);

        $session->remove();

        $instance = $session->getGeneralPlayer();

        if ($instance != null) $instance->extinguish();

        $arena->broadcastMessage($message);
    }
}