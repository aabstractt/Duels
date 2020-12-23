<?php

declare(strict_types=1);

namespace duels\math;

use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;
use RuntimeException;

class GamePosition extends GameVector3 {

    /** @var Level|null */
    private $world;

    /**
     * @param float|int $x
     * @param float|int $y
     * @param float|int $z
     * @param Level|null $world
     */
    public function __construct($x = 0, $y = 0, $z = 0, ?Level $world = null) {
        parent::__construct($x, $y, $z);

        $this->world = $world;
    }

    /**
     * @return Level|null
     */
    public function getWorld(): ?Level {
        return $this->world;
    }

    /**
     * @return Level
     */
    public function getWorldNonNull(): Level {
        $world = $this->getWorld();

        if ($world === null) {
            throw new RuntimeException('World null');
        }

        return $world;
    }

    /**
     * @return Vector3
     */
    public function get(): Vector3 {
        return new Position($this->getX(), $this->getY(), $this->getZ(), $this->getWorldNonNull());
    }
}