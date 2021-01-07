<?php

declare(strict_types=1);

namespace duels\kit;

use duels\Duels;
use duels\session\Session;
use duels\utils\ItemUtils;

class Kit {

    /** @var string */
    private $name;
    /** @var array */
    private $data;

    /**
     * Kit constructor.
     * @param string $name
     * @param array $data
     */
    public function __construct(string $name, array $data) {
        $this->name = $name;

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->name;
    }

    /**
     * @return bool
     */
    public function canBuild(): bool {
        return (bool) $this->data['canBuild'] ?? false;
    }

    /**
     * @return array
     */
    public function getData(): array {
        return $this->data;
    }

    public function handleUpdate(): void {
        Duels::getKitFactory()->createKit($this);
    }

    /**
     * @param Session $session
     */
    public function giveKit(Session $session): void {
        $instance = $session->getGeneralPlayer();

        $armor = $this->data['armor'] ?? [];

        $instance->getArmorInventory()->clearAll();

        foreach ($armor as $slot => $content) {
            $instance->getArmorInventory()->setItem($slot, ItemUtils::stringToItem($content));
        }

        $inventory = $this->data['inventory'] ?? [];

        $instance->getInventory()->clearAll();

        $effects = $this->data['effects'] ?? [];

        $instance->removeAllEffects();

        foreach ($inventory as $slot => $content) {
            $instance->getInventory()->setItem($slot, ItemUtils::stringToItem($content));
        }

        foreach ($effects as $effect) {
            $instance->addEffect(ItemUtils::stringToEffect($effect));
        }
    }
}