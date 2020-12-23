<?php

declare(strict_types=1);

namespace duels\math;

use pocketmine\level\Level;
use pocketmine\level\Location;
use pocketmine\math\Vector3;

class GameLocation extends GamePosition {

    /** @var float */
    private $yaw;
    /** @var float */
    private $pitch;

    /**
     * @param float|int $x
     * @param float|int $y
     * @param float|int $z
     * @param float $yaw
     * @param float $pitch
     * @param Level|null $world
     */
    public function __construct($x = 0, $y = 0, $z = 0, $yaw = 0.0, $pitch = 0.0, Level $world = null) {
        parent::__construct($x, $y, $z, $world);

        $this->yaw = $yaw;

        $this->pitch = $pitch;
    }

    /**
     * @return float
     */
    public function getYaw(): float {
        return $this->yaw;
    }

    /**
     * @return float
     */
    public function getPitch(): float {
        return $this->pitch;
    }

    /**
     * @return Vector3
     */
    public function get(): Vector3 {
        return new Location($this->getX(), $this->getY(), $this->getZ(), $this->getYaw(), $this->getPitch(), $this->getWorldNonNull());
    }

    /**
     * @param array $data
     * @param Level|null $level
     * @return GameLocation
     */
    public static function fromArray(array $data, Level $level = null): GameVector3 {
        return new GameLocation($data['x'], $data['y'], $data['z'], $data['yaw'], $data['pitch'], $level);
    }
}