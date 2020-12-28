<?php

declare(strict_types=1);

namespace duels\utils;

use duels\Duels;
use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\enchantment\Enchantment;
use pocketmine\item\enchantment\EnchantmentInstance;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\plugin\PluginException;
use pocketmine\utils\TextFormat;

class ItemUtils {

    /**
     * @param Item $item
     * @return string
     */
    public static function itemToString(Item $item): string {
        $data = $item->getId() . ':' . $item->getDamage() . ':' . $item->getCount();
        
        foreach ($item->getEnchantments() as $enchantment) {
            $data .= ':' . $enchantment->getId() . ';' . $enchantment->getLevel();
        }

        return $data;
    }

    /**
     * @param string $string
     * @return Item
     */
    public static function stringToItem(string $string): Item {
        if (strlen($string) < 2) {
            throw new PluginException('Invalid string');
        }

        $data = explode(':', $string);

        if (!isset($data[0], $data[1], $data[2])) {
            throw new PluginException('Invalid data');
        }

        $item = Item::get((int) $data[0], (int) $data[1], (int) $data[2]);

        for ($i = 3; $i < count($data); $i++) {
            if (!isset($data[$i])) continue;

            /** @var array<int, int> $enchantData */
            $enchantData = explode(';', $data[$i]);

            $enchantment = Enchantment::getEnchantment($enchantData[0]);

            if ($enchantment == null) continue;

            $item->addEnchantment(new EnchantmentInstance($enchantment, $enchantData[1]));
        }

        return $item;
    }

    /**
     * @param EffectInstance $effect
     * @return string
     */
    public static function effectToString(EffectInstance $effect): string {
        return $effect->getId() . ':' . $effect->getDuration() . ':' . $effect->getAmplifier();
    }

    /**
     * @param string $string
     * @return EffectInstance
     */
    public static function stringToEffect(string $string): EffectInstance {
        list($id, $duration, $amplifier) = explode(':', $string);

        $effect = Effect::getEffect((int)$id);

        if ($effect == null) {
            throw new PluginException('Invalid effect');
        }

        return new EffectInstance($effect, (int)$duration, (int)$amplifier);
    }

    /**
     * @return Item[]
     */
    public static function getLobbyItems(): array {
        $data = Duels::getInstance()->getConfig()->get('lobby-items', []);

        if (empty($data)) {
            return [];
        }

        if (!Duels::isLobbyItemsEnabled()) {
            return [];
        }

        /** @var Item[] $items */
        $items = [];

        foreach ($data as $name => $value) {
            $item = Item::get($value['id'], ($value['meta'] ?? 0), $value['count']);

            $item->setCustomName(TextFormat::colorize($value['name']));

            $tag = new ListTag('Commands');

            foreach ($value['commands'] as $command) {
                $tag->push(new StringTag('', $command['name'] . ':' . $command['console']));
            }

            $item->setCustomBlockData(new CompoundTag("", [new StringTag('Name', $name), $tag]));

            $items[$value['slot']] = $item;
        }

        return $items;
    }
}