<?php

declare(strict_types=1);

namespace duels\translation;

use duels\Duels;
use duels\InstancePluginReference;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Translation {

    use InstancePluginReference;

    /** @var array */
    private array $translations = [];

    public function init(): void {
        Duels::getInstance()->saveResource('translations.yml');

        $this->translations = (new Config(Duels::getInstance()->getDataFolder() . 'translations.yml'))->getAll();
    }

    /**
     * @param string $key
     * @param array $args
     * @return array
     */
    public function translateArray(string $key, array $args): array {
        $text = $this->translations[$key] ?? [];

        if (empty($text)) {
            return [];
        }

        foreach (array_keys($text) as $t) {
            foreach ($args as $i => $arg) {
                $text[$t] = TextFormat::colorize(str_replace('{' . $i . '}', $arg, $text[$t]));
            }
        }

        return $text;
    }
}