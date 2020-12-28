<?php

declare(strict_types=1);

namespace duels\provider;

use duels\Duels;
use duels\provider\command\StatsCommand;
use Exception;
use mysqli;
use pocketmine\Server;

class MysqlProvider {

    /** @var array */
    private $data;
    /** @var mysqli */
    private $mysql;

    /**
     * MysqlProvider constructor.
     * @param array $data
     * @throws Exception
     */
    public function __construct(array $data) {
        Server::getInstance()->getCommandMap()->register(StatsCommand::class, new StatsCommand());

        $this->data = $data;

        $connect = $this->connect();

        if ($connect == null) {
            throw new Exception('Mysql null');
        }

        try {
            if (!mysqli_query($connect, 'CREATE TABLE IF NOT EXISTS duels_stats (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(16), wins INT DEFAULT 0, losses INT DEFAULT 0)')) {
                throw new Exception(mysqli_error($connect));
            }
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);
        }
    }

    /**
     * @return mysqli|null
     * @throws Exception
     */
    public function connect(): ?mysqli {
        if ($this->mysql === null) {
            $this->mysql = @new mysqli($this->data['host'], $this->data['username'], $this->data['password'], $this->data['dbname'], $this->data['port']);

            if ($this->mysql->connect_errno) {
                throw new Exception($this->mysql->connect_error);
            }
        }

        if (!$this->hasConnection()) {
            $this->mysql->connect($this->data['host'], $this->data['username'], $this->data['password'], $this->data['dbname'], $this->data['port']);

            if ($this->mysql->connect_errno) {
                throw new Exception($this->mysql->connect_error);
            }
        }

        return $this->mysql->ping() ? $this->mysql : null;
    }

    /**
     * @param TargetOffline $targetOffline
     * @throws Exception
     */
    public function setTargetOffline(TargetOffline $targetOffline): void {
        $connect = $this->connect();

        if ($connect == null) return;

        try {
            if ($this->getTargetOffline($targetOffline->getName()) !== null) {
                $query = "UPDATE duels_stats SET wins = '{$targetOffline->getWins()}', losses = '{$targetOffline->getLosses()}' WHERE username = '{$targetOffline->getName()}'";
            } else {
                $query = "INSERT INTO duels_stats(username, wins, losses) VALUES ('{$targetOffline->getName()}', '{$targetOffline->getWins()}', '{$targetOffline->getLosses()}')";
            }

            if (!mysqli_query($connect, $query)) {
                throw new Exception($connect->error);
            }
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);
        }
    }

    /**
     * @param string $name
     * @return TargetOffline
     * @throws Exception
     */
    public function getTargetOffline(string $name): ?TargetOffline {
        $connect = $this->connect();

        if ($connect == null) return null;

        try {
            if (!($query = mysqli_query($connect, "SELECT * FROM duels_stats WHERE username = '{$name}'")) instanceof mysqli_result) {
                throw new Exception($connect->error);
            }

            if (mysqli_num_rows($query) <= 0) return null;

            $data = mysqli_fetch_assoc($query);

            if ($data == null) return null;

            return new TargetOffline($data);
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);
        }

        return null;
    }

    /**
     * @return TargetOffline[]
     * @throws Exception
     */
    public function getLeaderboard(): array {
        $connect = $this->connect();

        if ($connect == null) return [];

        /** @var TargetOffline[] $leaderboard */
        $leaderboard = [];

        try {
            if (!($query = mysqli_query($connect, "SELECT * FROM duels_stats ORDER BY wins DESC LIMIT 10")) instanceof mysqli_result) {
                throw new Exception($connect->error);
            }

            if (mysqli_num_rows($query) <= 0) return [];

            while($data = mysqli_fetch_assoc($query)) {
                $leaderboard[] = new TargetOffline($data);
            }

            return $leaderboard;
        } catch (Exception $e) {
            Duels::getInstance()->getLogger()->logException($e);
        }

        return [];
    }

    /**
     * @return bool
     */
    public function hasConnection(): bool {
        return $this->mysql != null && $this->mysql->ping();
    }
}