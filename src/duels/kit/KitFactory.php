<?php

declare(strict_types=1);

namespace duels\kit;

use duels\Duels;
use duels\kit\command\FFACommand;
use duels\kit\command\KitCommand;
use duels\kit\listener\BlockListener;
use duels\kit\listener\EntityListener;
use duels\kit\listener\InventoryListener;
use pocketmine\level\Level;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class KitFactory {

    /** @var array<string, Kit> */
    private $kits = [];
    /** @var array<string, FFA> */
    private $ffa = [];

    /**
     * KitFactory constructor.
     */
    public function __construct() {
        Server::getInstance()->getCommandMap()->register(KitCommand::class, new KitCommand());
        Server::getInstance()->getCommandMap()->register(FFACommand::class, new FFACommand());

        Duels::getInstance()->registerListeners(
            new BlockListener(),
            new EntityListener(),
            new InventoryListener()
        );

        foreach ((new Config(Duels::getInstance()->getDataFolder() . 'kits.json'))->getAll() as $name => $data) {
            $this->createKit(new Kit((string) $name, $data));
        }

        Server::getInstance()->getLogger()->info(TextFormat::AQUA . 'Duels: ' . count($this->kits) . ' kit(s) loaded.');
        Server::getInstance()->getLogger()->info(TextFormat::AQUA . 'Duels: ' . count($this->ffa) . ' ffa map(s) loaded.');
    }

    /**
     * @param Kit $kit
     */
    public function createKit(Kit $kit): void {
        $this->kits[strtolower($kit->getName())] = $kit;

        if (!$this->isFFA($kit->getName())) {
            Duels::getQueueFactory()->createQueue($kit);
        } else {
            $this->ffa[strtolower($kit->getName())] = new FFA($kit, Duels::getInstance()->getConfig()->getNested('ffa-worlds.' . $kit->getName(), ''));
        }

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

        if (!$this->isFFA($kitName)) {
            Duels::getQueueFactory()->removeQueue($kit);
        } else {
            $this->removeFFA($kitName);
        }

        $config = new Config(Duels::getInstance()->getDataFolder() . 'kits.json');

        $config->remove($kit->getName());

        $config->save();

        $config = Duels::getInstance()->getConfig();

        $data = $config->get('placeHolders', []);

        unset($data[$kit->getName()]);

        $config->set('placeHolders', $data);

        $config->save();

        unset($this->kits[strtolower($kitName)]);
    }

    /**
     * @return Kit[]
     */
    public function getKits(): array {
        return $this->kits;
    }

    /**
     * @return FFA[]
     */
    public function getKitsFFA(): array {
        return $this->ffa;
    }

    /**
     * @param string $kitName
     * @return FFA|null
     */
    public function getFFA(string $kitName): ?FFA {
        return $this->ffa[(!$this->isFFA($kitName) ? 'ffa_' : '') . strtolower($kitName)] ?? null;
    }

    /**
     * @param Level $world
     * @return FFA|null
     */
    public function getFFAByWorld(Level $world): ?FFA {
        foreach ($this->ffa as $ffa) {
            if ($ffa->getWorld()->getFolderName() !== $world->getFolderName()) continue;

            return $ffa;
        }

        return null;
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
            throw new KitException('Kit not found');
        }

        return $kit;
    }

    /**
     * @param string $kitName
     */
    public function removeFFA(string $kitName): void {
        $ffa = $this->getFFA($kitName);

        if ($ffa == null) return;

        $ffa->close();
    }

    /**
     * @param string $kitName
     * @return bool
     */
    public function isKit(string $kitName): bool {
        return isset($this->kits[strtolower($kitName)]);
    }

    /**
     * @param string $kitName
     * @return bool
     */
    public function isFFA(string $kitName): bool {
        return strpos(strtolower($kitName), 'ffa_') !== false;
    }
}