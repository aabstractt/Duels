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
    private $translations = [];

    public function init(): void {
        Duels::getInstance()->saveResource('scoreboard.yml');

        $this->translations = (new Config(Duels::getInstance()->getDataFolder() . 'scoreboard.yml'))->getAll();
    }

    /**
     * @param string $key
     * @param array $args
     * @return string
     */
    public function translateString(string $key, array $args = []): string {
        $text = $this->translations[$key] ?? [];

        if (empty($text)) {
            return '';
        }

        foreach ($args as $i => $arg) {
            $text = TextFormat::colorize(str_replace('{%' . $i . '}', $arg, $text));
        }

        return $text;
    }

    /**
     * @param string $key
     * @param array $args
     * @return array
     */
    public function translateArray(string $key, array $args = []): array {
        $text = $this->translations[$key] ?? [];

        if (empty($text)) {
            return [];
        }

        foreach (array_keys($text) as $t) {
            foreach ($args as $i => $arg) {
                $text[$t] = TextFormat::colorize(str_replace('{%' . $i . '}', $arg, $text[$t]));
            }
        }

        return $text;
    }
}