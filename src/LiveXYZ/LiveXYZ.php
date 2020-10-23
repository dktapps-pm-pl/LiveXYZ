<?php

/*
 *  LiveXYZ - a PocketMine-MP plugin to show your coordinates real-time as you move
 *  Copyright (C) 2016-2018 Dylan K. Taylor
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

declare(strict_types=1);

namespace LiveXYZ;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class LiveXYZ extends PluginBase implements Listener{
	/** @var TaskHandler[] */
	private $tasks = [];
	/** @var int */
	private $refreshRate = 1;
	/** @var string */
	private $mode = "popup";

	/** @var int */
	private $precision = 1;

	public function onEnable() : void{
		$this->refreshRate = (int) $this->getConfig()->get("refreshRate");
		if($this->refreshRate < 1){
			$this->getLogger()->warning("Refresh rate property in config.yml is less than 1. Resetting to 1");
			$this->getConfig()->set("refreshRate", 1);
			$this->getConfig()->save();
			$this->refreshRate = 1;
		}

		$this->mode = $this->getConfig()->get("displayMode", "popup");
		if($this->mode !== "tip" and $this->mode !== "popup"){
			$this->getLogger()->warning("Invalid display mode " . $this->mode . ", resetting to `popup`");
			$this->getConfig()->set("displayMode", "popup");
			$this->getConfig()->save();
			$this->mode = "popup";
		}
		$this->precision = (int) $this->getConfig()->get("precision");
		if($this->precision < 0){
			$this->getLogger()->warning("Precision property in config.yml is less than 0, using default");
			$this->getConfig()->set("precision", 1);
			$this->getConfig()->save();
			$this->precision = 1;
		}

		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onDisable() : void{
		//this doesn't get cleared on plugin disable, because plugins are not removed from the PluginManager unless
		//a reload took place. However, tasks ARE cancelled on plugin disable.
		$this->tasks = [];
	}

	public function onCommand(CommandSender $sender, Command $command, string $aliasUsed, array $args) : bool{
		if($command->getName() === "xyz"){
			if(!($sender instanceof Player)){
				$sender->sendMessage(TextFormat::RED . "You can't use this command in the terminal");

				return true;
			}

			if(!isset($this->tasks[$sender->getName()])){
				/** @var TaskHandler */
				$this->tasks[$sender->getName()] = $this->getScheduler()->scheduleRepeatingTask(new ShowDisplayTask($sender, $this->mode, $this->precision), $this->refreshRate);
				$sender->sendMessage(TextFormat::GREEN . "LiveXYZ is now on!");
			}else{
				$this->stopDisplay($sender->getName());
				$sender->sendMessage(TextFormat::GREEN . "LiveXYZ is now off.");
			}

			return true;
		}

		return false;
	}

	private function stopDisplay(string $playerFor) : void{
		if(isset($this->tasks[$playerFor])){
			$this->getScheduler()->cancelTask($this->tasks[$playerFor]->getTaskId());
			unset($this->tasks[$playerFor]);
		}
	}

	public function onPlayerQuit(PlayerQuitEvent $event) : void{
		$this->stopDisplay($event->getPlayer()->getName());
	}
}
