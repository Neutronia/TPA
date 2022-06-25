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

final class TPDenyCommand extends Command implements PluginOwned{
	use PluginOwnedTrait;

	public function __construct(){
		parent::__construct("tpdeny", "Deny a teleport request", "/tpdeny");
		$this->owningPlugin = Loader::getInstance();
		$this->setPermission("tpa.command.deny");
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
		if($queue->getReceiver()->getName() !== $sender->getName()){
			$sender->sendMessage(Loader::$prefix . "You can't deny a teleport request that is not sent to you.");
			return;
		}
		if(in_array($sender->getWorld()->getFolderName(), $this->owningPlugin->getConfig()->get("disallowed-worlds", []))){
			$sender->sendMessage(Loader::$prefix . "You cannot deny a teleport request in this world.");
			return;
		}
		$queue->deny();
	}
}