<?php

declare(strict_types=1);

namespace duels\arena;

use duels\Duels;
use duels\math\GameLocation;
use duels\math\GamePosition;
use duels\math\GameVector3;
use pocketmine\level\Location;

class Level {

    /** @var array */
    protected $data;

    /**
     * Level constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getFolderName(): string {
        return $this->data['folderName'];
    }

    /**
     * @return int
     */
    public function getMinSlots(): int {
        return (int) $this->data['minSlots'];
    }

    /**
     * @return int
     */
    public function getMaxSlots(): int {
        return (int) $this->data['maxSlots'];
    }

    /**
     * @param int $slot
     * @param Location $loc
     */
    public function addSlotPosition(int $slot, Location $loc): void {
        $this->data['spawns'][$slot] = GameVector3::toArray($loc);
    }

    /**
     * @param int $slot
     * @param \pocketmine\level\Level $level
     * @return GamePosition
     */
    public function getSlotPosition(int $slot, \pocketmine\level\Level $level): GamePosition {
        $data = $this->data['spawns'][$slot] ?? [];

        if (empty($data)) {
            $worldSpawn = $level->getSafeSpawn();

            return new GamePosition($worldSpawn->getFloorX(), $worldSpawn->getFloorY(), $worldSpawn->getFloorZ(), $level);
        }

        /** @var GameLocation $pos */
        $pos = GameLocation::fromArray($data, $level);

        return $pos;
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void {
        $this->data = $data;
    }

    public function handleUpdate(): void {
        Duels::getLevelFactory()->saveLevel($this);
    }
}