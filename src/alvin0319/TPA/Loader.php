<?php

declare(strict_types=1);

namespace alvin0319\TPA;

use alvin0319\TPA\command\TPAcceptCommand;
use alvin0319\TPA\command\TPDenyCommand;
use alvin0319\TPA\command\TPRequestCommand;
use pocketmine\event\EventPriority;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\ClosureTask;
use pocketmine\utils\SingletonTrait;
use function array_search;
use function array_values;
use function time;

final class Loader extends PluginBase{
	use SingletonTrait;

	public static string $prefix = "§l§6NT §f> §r§7";

	/** @var TPAQueue[] */
	private array $tpaQueue = [];

	protected function onLoad() : void{
		self::setInstance($this);
	}

	protected function onEnable() : void{
		$this->getScheduler()->scheduleRepeatingTask(new ClosureTask(function() : void{
			foreach($this->tpaQueue as $queue){
				if($queue->isExpired()){
					$queue->onExpired();
				}
			}
		}), 20);
		$this->getServer()->getPluginManager()->registerEvent(PlayerQuitEvent::class, function(PlayerQuitEvent $event) : void{
			$player = $event->getPlayer();
			$this->getQueue($player)?->onPlayerLeave();
		}, EventPriority::NORMAL, $this);
		$this->getServer()->getCommandMap()->registerAll("tpa", [
			new TPAcceptCommand(),
			new TPDenyCommand(),
			new TPRequestCommand()
		]);
	}

	public function addQueue(Player $sender, Player $receiver) : void{
		$queue = new TPAQueue($sender, $receiver, time());
		$this->tpaQueue[] = $queue;
	}

	public function hasQueue(Player $player) : bool{
		foreach($this->tpaQueue as $queue){
			if($queue->getSender()->getName() === $player->getName() || $queue->getReceiver()->getName() === $player->getName()){
				return true;
			}
		}
		return false;
	}

	public function getQueue(Player $player) : ?TPAQueue{
		foreach($this->tpaQueue as $queue){
			if($queue->getSender()->getName() === $player->getName() || $queue->getReceiver()->getName() === $player->getName()){
				return $queue;
			}
		}
		return null;
	}

	public function removeQueue(TPAQueue $queue) : void{
		$key = array_search($queue, $this->tpaQueue, true);
		if($key !== false){
			unset($this->tpaQueue[$key]);
			$this->tpaQueue = array_values($this->tpaQueue);
		}
	}
}