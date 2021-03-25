<?php

declare(strict_types=1);

namespace duels\duel;

use duels\api\Command;
use duels\Duels;
use duels\session\Session;
use pocketmine\command\CommandSender;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class DuelCommand extends Command {

    /**
     * DuelCommand constructor.
     */
    public function __construct() {
        parent::__construct('duel', 'Send duel a player', '/duel <player>');
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args): void {
        if (empty($args)) {
            throw new InvalidCommandSyntaxException();
        }

        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        $target = Server::getInstance()->getPlayer($args[0]);

        if ($target == null) {
            $sender->sendMessage(TextFormat::RED . 'Player not found');

            return;
        }

        if (strtolower($target->getName()) == strtolower($sender->getName())) {
            $sender->sendMessage(TextFormat::RED . 'You can\'t send duels to yourself');

            return;
        }

        if (Duels::getDuelFactory()->hasDuel($sender->getName(), $target->getName())) {
            $sender->sendMessage(TextFormat::RED . 'You have already sent a duel to ' . $target->getName());

            return;
        }

        $sessionTarget = Duels::getSessionFactory()->getSessionPlayer($target);

        if ($sessionTarget->inArena() || $sessionTarget->inFFA()) {
            $sender->sendMessage(TextFormat::RED . 'The player is already in an arena');

            return;
        }

        $session = Duels::getSessionFactory()->getSessionPlayer($sender);

        if ($session->inArena() || $session->inFFA()) {
            $sender->sendMessage(TextFormat::RED . 'You are already in an arena');

            return;
        }

        $data = [
            'type' => 'form',
            'title' => Duels::getInstance()->getConfig()->get('form-title-duel', ''),
            'content' => Duels::getInstance()->getConfig()->get('form-content-duel', ''),
            'buttons' => []
        ];

        $queues = Duels::getQueueFactory()->getQueuesUnranked();

        foreach ($queues as $queue) $data['buttons'][] = Duels::translatePlaceHolder($queue);

        $session->sendForm(function (Session $session, ?int $data) use($sessionTarget, $queues): void {
            if ($data === null) return;

            $queue = array_values($queues)[$data] ?? null;

            if ($queue == null) return;

            $session->sendMessage('&aDuel successfully sent to ' . $sessionTarget->getName());

            Duels::getDuelFactory()->addDuel($session->getName(), $sessionTarget->getName());

            $sessionTarget->sendForm(function (Session $sessionTarget, ?bool $data) use($session, $queue): void {
                if ($data === null) $data = false;

                if (($session->inArena() || $session->inFFA()) || ($sessionTarget->inArena() || $sessionTarget->inFFA())) $data = false;

                if ($data) {
                    Duels::getArenaFactory()->createArena([$session, $sessionTarget], $queue->isPremium(), $queue->getKit());
                } else {
                    Duels::getDuelFactory()->removeDuel($session->getName(), $sessionTarget->getName());
                }
            }, [
                'type' => 'modal',
                'title' => TextFormat::DARK_RED . TextFormat::BOLD . 'DUEL REQUEST',
                'content' => 'Duel received from ' . $session->getName() . "\nKit selected: " . $queue->getKit()->getName(),
                'button1' => 'Accept',
                'button2' => 'Deny'
            ]);
        }, $data);
    }
}