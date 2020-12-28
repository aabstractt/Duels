<?php

declare(strict_types=1);

namespace duels\duel;

class DuelFactory {

    /** @var array<string, array> */
    private $duels = [];

    /**
     * @param string $from
     * @param string $to
     */
    public function addDuel(string $from, string $to): void {
        $duels = $this->duels[strtolower($from)] ?? [];

        $this->duels[strtolower($from)] = array_merge($duels, [strtolower($to)]);
    }

    /**
     * @param string $from
     * @param string $to
     */
    public function removeDuel(string $from, string $to): void {
        $this->duels[strtolower($from)] = array_diff(($this->duels[strtolower($from)] ?? []), [strtolower($to)]);
    }

    /**
     * @param string $from
     */
    public function removeDuels(string $from): void {
        foreach (array_keys($this->duels) as $from2) {
            if (!$this->hasDuel($from2, $from)) continue;

            $this->removeDuel($from2, $from);
        }

        unset($this->duels[strtolower($from)]);
    }

    /**
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function hasDuel(string $from, string $to): bool {
        return in_array([strtolower($to)], ($this->duels[strtolower($from)] ?? []), true);
    }
}