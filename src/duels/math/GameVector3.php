<?php

declare(strict_types=1);

namespace duels\math;

use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;

class GameVector3 {

    /** @var float|int */
    private $x;
    /** @var float|int */
    private $y;
    /** @var float|int */
    private $z;

    /**
     * @param float|int $x
     * @param float|int $y
     * @param float|int $z
     */
    public function __construct($x = 0, $y = 0, $z = 0) {
        $this->x = $x;

        $this->y = $y;

        $this->z = $z;
    }

    /**
     * @return float|int
     */
    public function getX() {
        return $this->x;
    }

    /**
     * @return float|int
     */
    public function getY() {
        return $this->y;
    }

    /**
     * @return float|int
     */
    public function getZ() {
        return $this->z;
    }

    /**
     * @return Vector3
     */
    public function get(): Vector3 {
        return new Vector3($this->getX(), $this->getY(), $this->getZ());
    }

    /**
     * @param Vector3 $vector3
     * @return array
     */
    public static function toArray(Vector3 $vector3): array {
        $data = ['x' => $vector3->getFloorX(), 'y' => $vector3->getFloorY(), 'z' => $vector3->getFloorZ()];

        if ($vector3 instanceof Location) {
            $data = array_merge($data, ['yaw' => $vector3->yaw, 'pitch' => $vector3->pitch]);
        }

        return $data;
    }

    /**
     * @param array $data
     * @param Level|null $level
     * @return GameVector3|GameLocation
     */
    public static function fromArray(array $data, Level $level = null): GameVector3 {
        if ($level == null) {
            return new GameVector3($data['x'], $data['y'], $data['z']);
        }

        return new GameLocation($data['x'], $data['y'], $data['z'], $data['yaw'], $data['pitch'], $level);
    }
}