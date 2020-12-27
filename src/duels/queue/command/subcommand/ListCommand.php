<?php

declare(strict_types=1);

namespace duels\queue\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\Duels;
use duels\session\Session;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class ListCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        $queues = array_values(Duels::getQueueFactory()->getQueues());

        if (empty($queues)) return;

        $data = [
            'type' => 'form',
            'title' => TextFormat::BLUE . TextFormat::BOLD . 'Kit selection',
            'content' => '',
            'buttons' => []
        ];

        foreach ($queues as $queue) $data['buttons'][] = ['text' => Duels::translatePlaceHolder($queue)];

        $session->sendForm(function (Session $session, ?int $data) use($queues) : void {
            $queue = $queues[$data] ?? null;

            if ($queue == null) return;

            Server::getInstance()->dispatchCommand($session->getGeneralPlayer(), 'queue join ' . $queue->getKit()->getName());
        }, $data);
    }
}