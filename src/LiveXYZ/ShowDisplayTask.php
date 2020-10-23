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

use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;

class ShowDisplayTask extends Task{
	/** @var Player */
	private $player;
	/** @var string */
	private $mode;
	/** @var int */
	private $precision;

	public function __construct(Player $player, string $mode = "popup", int $precision = 1){
		$this->player = $player;
		$this->mode = $mode;
		$this->precision = $precision;
	}

	public function onRun() : void{
		assert(!$this->player->isClosed());
		$ploc = $this->player->getLocation();
		$location = "Location: " . TextFormat::GREEN . "(" . Utils::getFormattedCoords($this->precision, $ploc->getX(), $ploc->getY(), $ploc->getZ()) . ")" . TextFormat::WHITE . "\n";
		$world = "World: " . TextFormat::GREEN . $this->player->getWorld()->getDisplayName() . TextFormat::WHITE . "\n";
		$direction = "Direction: " . TextFormat::GREEN . Utils::getCompassDirection($ploc->getYaw() - 90) . " (" . round($ploc->getYaw(), $this->precision) . ")" . TextFormat::WHITE . "\n";

		switch($this->mode){
			case "tip":
				$this->player->sendTip($location . $world . $direction);
				break;
			case "popup":
				$this->player->sendPopup($location . $world . $direction);
				break;
			default:
				break;
		}
	}

}
