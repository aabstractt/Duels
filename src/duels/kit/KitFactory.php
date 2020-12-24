<?php

declare(strict_types=1);

namespace duels\kit;

use duels\Duels;
use duels\kit\command\KitCommand;
use pocketmine\plugin\PluginException;
use pocketmine\Server;
use pocketmine\utils\Config;

class KitFactory {

    /** @var array<string, Kit> */
    private $kits = [];

    /**
     * KitFactory constructor.
     */
    public function __construct() {
        Server::getInstance()->getCommandMap()->register(KitCommand::class, new KitCommand());

        foreach ((new Config(Duels::getInstance()->getDataFolder() . 'kits.json'))->getAll() as $name => $data) {
            $this->createKit(new Kit($name, $data));
        }
    }

    /**
     * @param Kit $kit
     */
    public function createKit(Kit $kit): void {
        $this->kits[strtolower($kit->getName())] = $kit;

        $config = new Config(Duels::getInstance()->getDataFolder() . 'kits.json');

        $config->set($kit->getName(), $kit->getData());

        $config->save();
    }

    /**
     * @param string $kitName
     */
    public function removeKit(string $kitName): void {
        $kit = $this->getKit($kitName);

        if ($kit == null) return;

        $config = new Config(Duels::getInstance()->getDataFolder() . 'kits.json');

        $config->remove($kit->getName());

        $config->save();

        unset($this->kits[strtolower($kitName)]);
    }

    /**
     * @param string $kitName
     * @return Kit|null
     */
    public function getKit(string $kitName): ?Kit {
        return $this->kits[strtolower($kitName)] ?? null;
    }

    /**
     * @param string $kitName
     * @return Kit
     */
    public function getKitNonNull(string $kitName): Kit {
        $kit = $this->getKit($kitName);

        if ($kit == null) {
            throw new PluginException('Kit not found');
        }

        return $kit;
    }
}