<?php

declare(strict_types=1);

namespace duels\provider;

class TargetOffline {

    /** @var array */
    protected $data;

    /**
     * TargetOffline constructor.
     * @param array $data
     */
    public function __construct(array $data) {
        if (!isset($data['gamesPlayed'])) {
            $data = [
                'username' => $data['username'],
                'wins' => 0,
                'losses' => 0
            ];
        }

        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getName(): string {
        return $this->data['username'];
    }

    /**
     * @return int
     */
    public function getWins(): int {
        return (int) $this->data['wins'];
    }

    public function increaseWins(): void {
        $this->data['wins']++;
    }

    /**
     * @return int
     */
    public function getLosses(): int {
        return (int) $this->data['losses'];
    }

    public function increaseLosses(): void {
        $this->data['losses']++;
    }
}