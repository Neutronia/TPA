<?php

declare(strict_types=1);

namespace alvin0319\TPA\command;

use alvin0319\TPA\Loader;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginOwned;
use pocketmine\plugin\PluginOwnedTrait;
use function in_array;

final class TPAcceptCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("tpaccept", "Accept a teleport request", "/tpaccept");
		$this->owningPlugin = Loader::getInstance();
		$this->setPermission("tpa.command.accept");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : void{
		if(!$this->testPermission($sender)){
			return;
		}
		if(!$sender instanceof Player){
			$sender->sendMessage(Loader::$prefix . "This command can only be used in-game.");
			return;
		}
		$queue = $this->owningPlugin->getQueue($sender);
		if($queue === null){
			$sender->sendMessage(Loader::$prefix . "You do not have a teleport request.");
			return;
		}
		if($queue->isExpired()){
			$sender->sendMessage(Loader::$prefix . "Your teleport request has expired.");
			return;
		}
		if(in_array($sender->getWorld()->getFolderName(), $this->owningPlugin->getConfig()->get("disallowed-worlds", []))){
			$sender->sendMessage(Loader::$prefix . "You cannot accept a teleport request in this world.");
			return;
		}
		if(in_array($queue->getSender()->getWorld()->getFolderName(), $this->owningPlugin->getConfig()->get("disallowed-worlds", []))){
			$sender->sendMessage(Loader::$prefix . "You cannot accept a teleport request because target is in the disallowed world.");
			return;
		}
		$queue->execute();
	}
}