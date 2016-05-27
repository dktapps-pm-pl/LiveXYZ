<?php

/*  LiveXYZ - a PocketMine-MP plugin to show your coordinates real-time as you move
    Copyright (C) 2016 Dylan K. Taylor

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.

*/

namespace LiveXYZ;

use pocketmine\plugin\PluginBase;
use pocketmine\event\Listener;

use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\Player;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

//Compatibility with PocketMine-MP
use LiveXYZ\Tasks\Callback;

// Otherwise use CallbackTask for distros which support it
use pocketmine\scheduler\CallbackTask;


class Main extends PluginBase implements Listener{
	private $players;
	private $refreshRate = 1;
	
	public function onEnable(){
		if(!is_dir($this->getDataFolder())){
            mkdir($this->getDataFolder());
        }
		if(!file_exists($this->getDataFolder() . "config.yml")){
			$this->saveDefaultConfig();
		}
		
		$this->refreshRate = intval($this->getConfig()->get("refreshRate"));
		if($this->refreshRate < 1){
			$this->getServer()->getLogger()->warning("[LiveXYZ] Refresh rate property in config.yml is less than 1. Resetting to 1");
			$this->getConfig()->set("refreshRate",1);
			$this->getConfig()->save();
			$this->refreshRate = 1;
		}
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->players = array(false);
	}
	
	public function onDisable(){
		unset($this->players);
	}
	
	public function onCommand(CommandSender $sender, Command $command, $label, array $args){
		if(strtolower($command->getName()) == "xyz"){
			if(!$sender instanceof Player){
				$sender->sendMessage("§cYou can't use this command in the terminal");
				return true;
			}
			if(!$sender->hasPermission("livexyz")){
				$sender->sendMessage("§cYou are not permitted to use this command");
				return true;
			}
			if(!isset($this->players[$sender->getName()])){
				$this->players[$sender->getName()] = [];
				$this->players[$sender->getName()]["xyz"] = false;
				$this->players[$sender->getName()]["padding"] = 0;
				$this->players[$sender->getName()]["shownTip"] = false;
			}
			
			//Quick hack to fix undefined property errors
			if(!isset($this->players[$sender->getName()]["xyz"])){
				$this->players[$sender->getName()]["xyz"] = false;
			}
			$this->players[$sender->getName()]["xyz"] = !$this->players[$sender->getName()]["xyz"];	
			$sender->sendMessage("§aLiveXYZ is now ".($this->players[$sender->getName()]["xyz"]? "on!": "off."));
			if($this->players[$sender->getName()]["xyz"]){
				$this->showXYZ($sender);
			}
			return true;
		}
	}
	
	public function onPlayerQuit(PlayerQuitEvent $event){
		unset($this->players[$event->getPlayer()->getName()]["xyz"]);
	}
	
	public function bearing($deg) {
		//https://github.com/Muirfield/pocketmine-plugins/blob/master/GrabBag/src/aliuly/common/ExpandVars.php
		// Determine bearing in degrees
		if (22.5 <= $deg && $deg < 67.5) {
			return "Northwest";
		}elseif (67.5 <= $deg && $deg < 112.5) {
			return "North";
		}elseif (112.5 <= $deg && $deg < 157.5) {
			return "Northeast";
		}elseif (157.5 <= $deg && $deg < 202.5) {
			return "East";
		}elseif (202.5 <= $deg && $deg < 247.5) {
			return "Southeast";
		}elseif (247.5 <= $deg && $deg < 292.5) {
			return "South";
		}elseif (292.5 <= $deg && $deg < 337.5) {
			return "Southwest";
		}else {
			return "West";
		}
		return (int)$deg;
	}
	
	public function showXYZ(Player $player){
		if(!isset($this->players[$player->getName()]["xyz"]) or !$this->players[$player->getName()]["xyz"]){
			return;
		}
		$xyz = "Position: §a(".number_format($player->getX(),1,".",",").", ".number_format($player->getY(),1,".",",").", ".number_format($player->getZ(),1,".",",").")";
		$world = "World: §a".$player->getLevel()->getName();
		$direction = "Facing §a".$this->bearing($player->getYaw())." (".$player->getYaw().")";
		$player->sendPopup(str_repeat("\n", $this->players[$player->getName()]["padding"]).$xyz."§f\n".$world."§f\n".$direction);
		try{
			$this->getServer()->getScheduler()->scheduleDelayedTask(new CallbackTask([$this, "showXYZ"], [$player]), $this->refreshRate);
		}catch(\Exception $e){
			//Maintain functionality on PocketMine-MP
			$this->getServer()->getScheduler()->scheduleDelayedTask(new Callback([$this, "showXYZ"], [$player]), $this->refreshRate);
		}
		
	}
}