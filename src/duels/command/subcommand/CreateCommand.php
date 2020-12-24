<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\asyncio\FileCopyAsyncTask;
use duels\Duels;
use duels\session\Session;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CreateCommand extends PlayerSubCommand {

    /**
     * @param Session $session
     * @param array $args
     */
    public function onRun(Session $session, array $args): void {
        if (empty($args[0])) {
            $session->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <kit>');

            return;
        }

        $level = $session->getLevelNonNull();

        if ($level === Server::getInstance()->getDefaultLevel()) {
            $session->sendMessage(TextFormat::RED . 'You can\'t setup maps in the lobby.');

            return;
        }

        if (isset(Duels::getLevelFactory()->getAllLevels()[strtolower($level->getFolderName())])) {
            $session->sendMessage(TextFormat::RED . 'This arena already exists.');

            return;
        }

        if (!Duels::getKitFactory()->isKit($args[0])) {
            $session->sendMessage(TextFormat::RED . 'Kit not found');

            return;
        }

        $level->save(true);

        $data = [
            'folderName' => $level->getFolderName(),
            'kit' => $args[0],
            'minSlots' => 2,
            'maxSlots' => 2,
            'spawns' => []
        ];

        Server::getInstance()->getAsyncPool()->submitTask(new FileCopyAsyncTask(Server::getInstance()->getDataPath() . '/worlds/' . $data['folderName'], Duels::getInstance()->getDataFolder() . '/arenas/' . $data['folderName'], function () use ($session, $data) {
            Duels::getLevelFactory()->loadLevel($data)->handleUpdate();

            $session->sendMessage(TextFormat::GREEN . 'Successfully created ' . $data['folderName']);
        }));
    }
}