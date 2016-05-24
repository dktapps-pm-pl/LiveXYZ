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

use pocketmine\scheduler\Task;

class Main extends PluginBase implements Listener{
	private $players;
	
	public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		$this->players = array(false);
		
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
			/*
			if(isset($args[0])){
				if(is_numeric($args[0])){
					$this->players[$sender->getName()]["padding"] = intval($args[0]);
					$sender->sendMessage("§aDialog top padding set to ".intval($args[0]));
					return true;
				}else{
					$sender->sendMessage("§cPadding number must be numeric");
					return true;
				}
			}*/
			
			//Quick hack to fix undefined property errors
			if(!isset($this->players[$sender->getName()]["xyz"])){
				$this->players[$sender->getName()]["xyz"] = false;
			}
			$this->players[$sender->getName()]["xyz"] = !$this->players[$sender->getName()]["xyz"];	
			$sender->sendMessage("§aLiveXYZ is now ".($this->players[$sender->getName()]["xyz"]? "on!": "off."));
			if($this->players[$sender->getName()]["xyz"]){
				/*if(!$this->players[$sender->getName()]["shownTip"]){
					$sender->sendMessage("§aPro tip: if the dialog is too high/low, say /xyz <number> to change the top padding.");
					$this->players[$sender->getName()]["shownTip"] = true;
				}*/
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
    // Determine bearing
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
		
		//$strLength = (floor(max(strlen($xyz), strlen($world), strlen($direction))/2))*2;
		
		//$this->padString($xyz,$strLength);
		//$this->padString($world,$strLength);
		//$this->padString($direction,$strLength);
		
		//Use sendPopup, sendTip currently broken
		$player->sendPopup(str_repeat("\n", $this->players[$player->getName()]["padding"]).$xyz."§f\n".$world."§f\n".$direction);
		
		$this->getServer()->getScheduler()->scheduleDelayedTask(new Callback([$this, "showXYZ"], [$player]), 1);
	}
	
	public function padString(&$string, $finalLength){
		
		/* Let's say that finalLength is 6 and the input is "yea"
		 * the result will be " yea  "
		 * If the input is "yeah"
		 * the result will be " yeah "
		 */
		while(strlen($string) < $finalLength){
			if(strlen($string) < $finalLength){
				$string = " ".$string;
			}
			if(strlen($string) < $finalLength){
				$string = $string." ";
			}
			//$string = " ".$string." ";
		}
		/*if(strlen($string) !== $finalLength){
			$string = $string." ";
		}*/
	}
}

class Callback extends Task{

	public function __construct(callable $callable, array $args = array()){
		$this->callable = $callable;
		$this->args = $args;
		$this->args[] = $this;
	}

	public function getCallable(){
		return $this->callable;
	}

	public function onRun($currentTicks){
		call_user_func_array($this->callable, $this->args);
	}
}
