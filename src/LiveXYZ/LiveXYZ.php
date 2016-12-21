<?php

/*
 *  LiveXYZ - a PocketMine-MP plugin to show your coordinates real-time as you move
 *  Copyright (C) 2016 Dylan K. Taylor
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

namespace LiveXYZ;

use pocketmine\Player;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\plugin\PluginBase;
use pocketmine\scheduler\TaskHandler;
use pocketmine\utils\TextFormat;

class LiveXYZ extends PluginBase implements Listener{
	/** @var TaskHandler[] */
	private $tasks = [];
	private $refreshRate = 1;
	private $mode = "popup";
	
	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
            @mkdir($this->getDataFolder());
        }
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->saveDefaultConfig();
		}
		
		$this->refreshRate = intval($this->getConfig()->get("refreshRate"));
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
		
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}

	public function onCommand(CommandSender $sender, Command $command, $aliasUsed = "", array $args){
		if($command->getName() === "xyz"){
			if(!$sender instanceof Player){
				$sender->sendMessage(TextFormat::RED . "You can't use this command in the terminal");
				return true;
			}
			if(!$sender->hasPermission("livexyz")){
				$sender->sendMessage(TextFormat::RED . "You are not permitted to use this command");
				return true;
			}
			
			if(!isset($this->tasks[$sender->getName()])){
				/** @var TaskHandler */
				$this->tasks[$sender->getName()] = $sender->getServer()->getScheduler()->scheduleRepeatingTask(new ShowDisplayTask($this, $sender, $this->mode), $this->refreshRate);
				$sender->sendMessage(TextFormat::GREEN . "LiveXYZ is now on!");
			}else{
				$sender->getServer()->getScheduler()->cancelTask($this->tasks[$sender->getName()]->getTaskId());
				unset($this->tasks[$sender->getName()]);
				$sender->sendMessage(TextFormat::GREEN . "LiveXYZ is now off.");
			}
			
			return true;
		}
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		unset($this->tasks[$event->getPlayer()->getName()]);
	}
}
