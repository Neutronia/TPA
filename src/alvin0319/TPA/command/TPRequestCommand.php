<?php

declare(strict_types=1);

namespace alvin0319\TPA\command;

use alvin0319\TPA\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function array_shift;
use function count;

final class TPRequestCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("tpa", "Request a teleport", "/tpa");
		$this->owningPlugin = Loader::getInstance();
		$this->setPermission("tpa.command.request");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Loader::$prefix . "This command can only be used in-game.");
			return;
		}
		if(count($args) < 1){
			$sender->sendMessage(Loader::$prefix . "Usage: /tpa <player>");
			return;
		}
		$receiver = $sender->getServer()->getPlayerByPrefix(array_shift($args));
		if($receiver === null){
			$sender->sendMessage(Loader::$prefix . "Player not found.");
			return;
		}
		if($receiver === $sender){
			$sender->sendMessage(Loader::$prefix . "You cannot request a teleport to yourself.");
			return;
		}
		if($this->owningPlugin->hasQueue($sender)){
			$sender->sendMessage(Loader::$prefix . "You already have a teleport request.");
			return;
		}
		if($this->owningPlugin->hasQueue($receiver)){
			$sender->sendMessage(Loader::$prefix . "That player already has a teleport request.");
			return;
		}
		Loader::getInstance()->addQueue($sender, $receiver);
		$sender->sendMessage(Loader::$prefix . "Teleport request has been sent.");
		$receiver->sendMessage(Loader::$prefix . "{$sender->getName()} has requested to teleport to you. Type /tpaccept to accept or /tpdeny to deny. You have 1 minute to accept or deny the request.");
	}
}