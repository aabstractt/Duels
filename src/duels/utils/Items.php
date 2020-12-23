<?php

declare(strict_types=1);

namespace duels\utils;

use duels\Duels;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\utils\TextFormat;

class Items {

    /**
     * @return Item[]
     */
    public static function getLobbyItems(): array {
        $data = Duels::getInstance()->getConfig()->get('lobby-items');

        if (empty($data)) {
            return [];
        }

        /** @var Item[] $items */
        $items = [];

        foreach ($data as $name => $value) {
            $item = Item::get($value['id'], ($value['meta'] ?? 0), $value['count']);

            $item->setCustomName(TextFormat::colorize($value['name']));
            $item->setCustomBlockData(new CompoundTag("", [new StringTag('Name', $name), new StringTag('Command', $value['command'] ?? '')]));

            $items[$value['slot']] = $item;
        }

        return $items;
    }

    /**
     * @return Item[]
     */
    public static function getSpectatorItems(): array {
        $data = Duels::getInstance()->getConfig()->get('spectator-items');

        if (empty($data)) {
            return [];
        }

        /** @var Item[] $items */
        $items = [];

        foreach ($data as $name => $value) {
            $item = Item::get($value['id'], ($value['meta'] ?? 0), $value['count']);

            $item->setCustomName(TextFormat::colorize($value['name']));
            $item->setCustomBlockData(new CompoundTag("", [new StringTag('Name', $name), new StringTag('Command', $value['command'] ?? '')]));

            $items[$value['slot']] = $item;
        }

        return $items;
    }

    /**
     * @return Item[]
     */
    public static function getGameEndItems(): array {
        $data = Duels::getInstance()->getConfig()->get('game-end-items');

        if (empty($data)) {
            return [];
        }

        /** @var Item[] $items */
        $items = [];

        foreach ($data as $name => $value) {
            $item = Item::get($value['id'], ($value['meta'] ?? 0), $value['count']);

            $item->setCustomName(TextFormat::colorize($value['name']));
            $item->setCustomBlockData(new CompoundTag("", [new StringTag('Name', $name), new StringTag('Command', $value['command'] ?? '')]));

            $items[$value['slot']] = $item;
        }

        return $items;
    }
}