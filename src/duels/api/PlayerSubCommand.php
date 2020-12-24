<?php

declare(strict_types=1);

namespace duels\api;

use duels\Duels;
use duels\session\Session;
use duels\session\SessionException;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

abstract class PlayerSubCommand extends SubCommand {

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function run(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        try {
            $this->onRun(Duels::getSessionFactory()->getSessionPlayer($sender), $args);
        } catch (SessionException $exception) {
            $sender->kick($exception->getMessage());
        }
    }

    /**
     * @param Session $session
     * @param array $args
     */
    public abstract function onRun(Session $session, array $args): void;
}