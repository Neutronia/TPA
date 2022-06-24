<?php

declare(strict_types=1);

namespace alvin0319\TPA;

use pocketmine\player\Player;
use function time;

final class TPAQueue{

	public function __construct(private Player $sender, private Player $receiver, private int $requestedAt){ }

	public function getSender() : Player{
		return $this->sender;
	}

	public function getReceiver() : Player{
		return $this->receiver;
	}

	public function getRequestedAt() : int{
		return $this->requestedAt;
	}

	public function isExpired() : bool{
		return time() - $this->requestedAt > 60;
	}

	public function execute() : void{
		$this->sender->teleport($this->receiver->getPosition());
		$this->sender->sendMessage(Loader::$prefix . "Teleport request has been accepted.");
		$this->receiver->sendMessage(Loader::$prefix . "{$this->sender->getName()} has teleported to you.");
		Loader::getInstance()->removeQueue($this);
	}

	public function deny() : void{
		$this->sender->sendMessage(Loader::$prefix . "{$this->receiver->getName()} has declined your teleport request.");
		$this->receiver->sendMessage(Loader::$prefix . "You have declined {$this->sender->getName()}'s teleport request.");
		Loader::getInstance()->removeQueue($this);
	}

	public function onExpired() : void{
		$this->sender->sendMessage(Loader::$prefix . "Your teleport request has expired.");
		$this->receiver->sendMessage(Loader::$prefix . "{$this->sender->getName()}'s teleport request has expired.");
		Loader::getInstance()->removeQueue($this);
	}

	public function onPlayerLeave() : void{
		if($this->sender->isConnected()){
			$this->receiver->sendMessage(Loader::$prefix . "{$this->sender->getName()} has left the game.");
		}
		if($this->receiver->isConnected()){
			$this->receiver->sendMessage(Loader::$prefix . "{$this->sender->getName()} has left the game.");
		}
		Loader::getInstance()->removeQueue($this);
	}
}