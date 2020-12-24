<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\PlayerSubCommand;
use duels\asyncio\FileCopyAsyncTask;
use duels\Duels;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CreateCommand extends PlayerSubCommand {

    /**
     * @param Player $player
     * @param array $args
     */
    public function onRun(Player $player, array $args): void {
        if (empty($args[0])) {
            $player->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <kit>');

            return;
        }

        $level = $player->getLevelNonNull();

        if ($level === Server::getInstance()->getDefaultLevel()) {
            $player->sendMessage(TextFormat::RED . 'You can\'t setup maps in the lobby.');

            return;
        }

        if (isset(Duels::getLevelFactory()->getAllLevels()[strtolower($level->getFolderName())])) {
            $player->sendMessage(TextFormat::RED . 'This arena already exists.');

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

        Server::getInstance()->getAsyncPool()->submitTask(new FileCopyAsyncTask(Server::getInstance()->getDataPath() . '/worlds/' . $data['folderName'], Duels::getInstance()->getDataFolder() . '/arenas/' . $data['folderName'], function () use ($player, $data) {
            Duels::getLevelFactory()->loadLevel($data)->handleUpdate();

            $player->sendMessage(TextFormat::GREEN . 'Successfully created ' . $data['folderName']);
        }));
    }
}