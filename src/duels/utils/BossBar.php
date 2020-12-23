<?php

declare(strict_types=1);

namespace duels\utils;

use duels\arena\Arena;
use duels\session\Session;
use pocketmine\entity\Attribute;
use pocketmine\entity\Entity;
use pocketmine\entity\EntityIds;
use pocketmine\network\mcpe\protocol\AddActorPacket;
use pocketmine\network\mcpe\protocol\BossEventPacket;
use pocketmine\network\mcpe\protocol\RemoveActorPacket;
use pocketmine\network\mcpe\protocol\SetActorDataPacket;
use pocketmine\network\mcpe\protocol\UpdateAttributesPacket;

class BossBar {

    /** @var int */
    private $id;
    /** @var Arena */
    private $arena;
    /** @var string */
    private $title;
    /** @var string */
    private $subtitle;
    /** @var int */
    private $percentage = 100;

    /**
     * BossBar constructor.
     * @param Arena $arena
     * @param string $title
     * @param string $subtitle
     */
    public function __construct(Arena $arena, string $title, string $subtitle) {
        $this->id = Entity::$entityCount++;

        $this->arena = $arena;

        $this->title = $title;

        $this->subtitle = $subtitle;
    }

    /**
     * @param Session|null $player
     * @param string|null $title
     * @param string|null $subtitle
     */
    public function addPlayer(Session $player = null, ?string $title = null, string $subtitle = null): void {
        $players = $this->arena->getAllPlayers();

        if ($player !== null) $players = [$player];

        if ($title == null) $title = $this->title;
        if ($subtitle == null) $subtitle = $this->subtitle;

        $pk = new AddActorPacket();

        $pk->entityRuntimeId = $this->id;

        $pk->type = AddActorPacket::LEGACY_ID_MAP_BC[EntityIds::SLIME];

        $pk->metadata = [Entity::DATA_LEAD_HOLDER_EID => [Entity::DATA_TYPE_LONG, -1], Entity::DATA_FLAGS => [Entity::DATA_TYPE_LONG, 0 ^ 1 << Entity::DATA_FLAG_SILENT ^ 1 << Entity::DATA_FLAG_INVISIBLE ^ 1 << Entity::DATA_FLAG_NO_AI], Entity::DATA_SCALE => [Entity::DATA_TYPE_FLOAT, 0],
            Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title . "\n" . $subtitle], Entity::DATA_BOUNDING_BOX_WIDTH => [Entity::DATA_TYPE_FLOAT, 0], Entity::DATA_BOUNDING_BOX_HEIGHT => [Entity::DATA_TYPE_FLOAT, 0]];

        foreach ($players as $player) {
            $this->removePlayer($player);

            $instance = $player->getGeneralPlayer();

            $pk->position = $instance->getPosition();

            $instance->sendDataPacket($pk);
        }

        $bpk = new BossEventPacket();
        $bpk->bossEid = $this->id;
        $bpk->eventType = BossEventPacket::TYPE_SHOW;
        $bpk->title = $title . "\n" . $subtitle;
        $bpk->healthPercent = 1;
        $bpk->unknownShort = 0;
        $bpk->color = 0;
        $bpk->overlay = 0;
        $bpk->playerEid = 0;

        foreach ($players as $player) {
            $player->getGeneralPlayer()->sendDataPacket($bpk);
        }
    }

    /**
     * @param string $title
     * @param string|null $subtitle
     * @param Session|null $player
     */
    public function setTitle(string $title, ?string $subtitle = null, Session $player = null): void {
        $players = $this->arena->getAllPlayers();

        if ($player !== null) $players = [$player];

        $this->title = $title;

        if ($subtitle == null) $subtitle = $this->subtitle;

        $this->subtitle = $subtitle;

        $pk = new SetActorDataPacket();

        $pk->metadata = [Entity::DATA_NAMETAG => [Entity::DATA_TYPE_STRING, $title . "\n" . $subtitle]];

        $pk->entityRuntimeId = $this->id;

        foreach ($players as $player) {
            $player->getGeneralPlayer()->sendDataPacket($pk);
        }

        $pk = new BossEventPacket();
        $pk->bossEid = $this->id;
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->title = $title . "\n" . $subtitle;
        $pk->healthPercent = $this->percentage;
        $pk->unknownShort = 0;
        $pk->color = 0;
        $pk->overlay = 0;
        $pk->playerEid = 0;

        foreach ($players as $player) {
            $player->getGeneralPlayer()->sendDataPacket($pk);
        }
    }

    /**
     * @param int $percentage
     */
    public function setPercentage(int $percentage): void {
        $attribute = Attribute::getAttribute(Attribute::HEALTH);

        if ($attribute == null) return;

        $attribute->setMinValue(1);
        $attribute->setMaxValue(600);
        $attribute->setValue(max(1, min([$percentage, 100])) / 100 * 600);

        $pk = new UpdateAttributesPacket();
        $pk->entries[] = $attribute;
        $pk->entityRuntimeId = $this->id;

        foreach ($this->arena->getAllPlayers() as $player) {
            $player->getGeneralPlayer()->sendDataPacket($pk);
        }

        $pk = new BossEventPacket();
        $pk->bossEid = $this->id;
        $pk->eventType = BossEventPacket::TYPE_SHOW;
        $pk->title = "";
        $pk->healthPercent = $percentage / 100;
        $pk->unknownShort = 0;
        $pk->color = 0;
        $pk->overlay = 0;
        $pk->playerEid = 0;

        foreach ($this->arena->getAllPlayers() as $player) {
            $player->getGeneralPlayer()->sendDataPacket($pk);
        }
    }

    /**
     * @param Session|null $player
     */
    public function removePlayer(Session $player = null): void {
        $players = $this->arena->getAllPlayers();

        if ($player !== null) $players = [$player];

        $pk = new RemoveActorPacket();

        $pk->entityUniqueId = $this->id;

        foreach ($players as $player) {
            $player->getGeneralPlayer()->sendDataPacket($pk);
        }
    }
}