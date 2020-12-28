<?php

declare(strict_types=1);

namespace duels\api;

use duels\Duels;
use pocketmine\form\Form as IForm;
use pocketmine\Player;

class Form implements IForm {

    /** @var callable */
    private $callback;
    /** @var array */
    private $data;

    /**
     * Form constructor.
     * @param callable $callback
     * @param array $data
     */
    public function __construct(callable $callback, array $data) {
        $this->callback = $callback;

        $this->data = $data;
    }

    /**
     * Handles a form response from a player.
     *
     * @param Player $player
     * @param int|bool|null $data
     */
    public function handleResponse(Player $player, $data): void {
        ($this->callback)(Duels::getSessionFactory()->getSessionPlayer($player), $data);
    }

    /**
     * Specify data which should be serialized to JSON
     * @link https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return array data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize(): array {
        return $this->data;
    }
}