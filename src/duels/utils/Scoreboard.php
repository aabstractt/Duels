<?php

declare(strict_types=1);

namespace duels\utils;

use duels\arena\Arena;
use duels\session\Session;
use pocketmine\network\mcpe\protocol\DataPacket;
use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\utils\TextFormat;

class Scoreboard {

    /** @var string */
    public const LIST = 'list';
    public const SIDEBAR = 'sidebar';

    /** @var int */
    public const ASCENDING = 0;
    public const DESCENDING = 1;

    /** @var Arena */
    private $arena;
    /** @var string */
    public $displayName;
    /** @var string */
    private $objectiveName;
    /** @var string */
    private $displaySlot;
    /** @var int */
    private $sortOrder;

    /**
     * Scoreboard constructor.
     * @param Arena $arena
     * @param string $title
     * @param string $displaySlot
     * @param int $sortOrder
     */
    public function __construct(Arena $arena, string $title, string $displaySlot, int $sortOrder = self::DESCENDING) {
        $this->arena = $arena;

        $this->displayName = $title;

        $this->objectiveName = uniqid('', true);

        $this->displaySlot = $displaySlot;

        $this->sortOrder = $sortOrder;
    }

    /**
     * @param Session|null $player
     */
    public function removePlayer(Session $player = null): void {
        $players = [];

        if ($player !== null) $players = [$player];

        $pk = new RemoveObjectivePacket();

        $pk->objectiveName = $this->objectiveName;

        foreach ($players as $p) {
            $p->getGeneralPlayer()->sendDataPacket($pk);
        }
    }

    /**
     * @param Session|null $player
     */
    public function addPlayer(Session $player = null): void {
        $players = $this->arena->getAllPlayers();

        if ($player !== null) $players = [$player];

        $pk = new SetDisplayObjectivePacket();

        $pk->displaySlot = $this->displaySlot;

        $pk->objectiveName = $this->objectiveName;

        $pk->displayName = $this->displayName;

        $pk->criteriaName = 'dummy';

        $pk->sortOrder = $this->sortOrder;

        foreach ($players as $p ) {
            $p->getGeneralPlayer()->sendDataPacket($pk);
        }
    }

    /**
     * @param int $line
     * @param string $message
     * @param Session|null $player
     */
    public function setLine(int $line, string $message = '', Session $player = null): void {
        $this->setLines([$line => $message], $player);
    }

    /**
     * @param array $lines
     * @param Session|null $player
     */
    public function setLines(array $lines, ?Session $player = null): void {
        $players = $this->arena->getAllPlayers();

        if ($player !== null) $players = [$player];

        foreach ($players as $p) {
            $instance = $p->getGeneralPlayer();

            $instance->sendDataPacket($this->getPackets($lines, SetScorePacket::TYPE_REMOVE));

            $instance->sendDataPacket($this->getPackets($lines, SetScorePacket::TYPE_CHANGE));
        }
    }

    /**
     * @param array $lines
     * @param int $type
     * @return DataPacket
     */
    public function getPackets(array $lines, int $type): DataPacket {
        $pk = new SetScorePacket();

        $pk->type = $type;

        foreach ($lines as $line => $message) {
            $entry = new ScorePacketEntry();

            $entry->objectiveName = $this->objectiveName;

            $entry->score = $line;

            $entry->scoreboardId = $line;

            if ($type === SetScorePacket::TYPE_CHANGE) {
                if ($message === '') {
                    $message = str_repeat(' ', $line - 1);
                }

                $entry->type = ScorePacketEntry::TYPE_FAKE_PLAYER;

                $entry->customName = TextFormat::colorize($message) . ' ';
            }

            $pk->entries[] = $entry;
        }

        return $pk;
    }
}