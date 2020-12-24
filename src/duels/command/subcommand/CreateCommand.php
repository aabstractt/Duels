<?php

declare(strict_types=1);

namespace duels\command\subcommand;

use duels\api\SubCommand;
use duels\asyncio\FileCopyAsyncTask;
use duels\Duels;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

class CreateCommand extends SubCommand {

    /**
     * @param CommandSender $sender
     * @param array $args
     */
    public function run(CommandSender $sender, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TextFormat::RED . 'Run this command in-game');

            return;
        }

        if (empty($args[0])) {
            $sender->sendMessage(TextFormat::RED . 'Usage: /config ' . $this->getName() . ' <kit>');

            return;
        }

        $level = $sender->getLevelNonNull();

        if ($level === Server::getInstance()->getDefaultLevel()) {
            $sender->sendMessage(TextFormat::RED . 'You can\'t setup maps in the lobby.');

            return;
        }

        if (isset(Duels::getLevelFactory()->getAllLevels()[strtolower($level->getFolderName())])) {
            $sender->sendMessage(TextFormat::RED . 'This arena already exists.');

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

        Server::getInstance()->getAsyncPool()->submitTask(new FileCopyAsyncTask(Server::getInstance()->getDataPath() . '/worlds/' . $data['folderName'], Duels::getInstance()->getDataFolder() . '/arenas/' . $data['folderName'], function () use ($sender, $data) {
            Duels::getLevelFactory()->loadLevel($data)->handleUpdate();

            $sender->sendMessage(TextFormat::GREEN . 'Successfully created ' . $data['folderName']);
        }));
    }
}