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

declare(strict_types=1);

namespace LiveXYZ;

class Utils{

	public static function getCompassDirection(float $deg) : string{
		//https://github.com/Muirfield/pocketmine-plugins/blob/master/GrabBag/src/aliuly/common/ExpandVars.php
		//Determine bearing in degrees
		$deg %= 360;

		if(22.5 <= $deg and $deg < 67.5){
			return "Northwest";
		}elseif(67.5 <= $deg and $deg < 112.5){
			return "North";
		}elseif(112.5 <= $deg and $deg < 157.5){
			return "Northeast";
		}elseif(157.5 <= $deg and $deg < 202.5){
			return "East";
		}elseif(202.5 <= $deg and $deg < 247.5){
			return "Southeast";
		}elseif(247.5 <= $deg and $deg < 292.5){
			return "South";
		}elseif(292.5 <= $deg and $deg < 337.5){
			return "Southwest";
		}else{
			return "West";
		}
	}

	public static function getFormattedCoords(float ...$coords) : string{
		foreach($coords as &$c){
			$c = number_format($c, 1, ".", ",");
		}
		return implode(", ", $coords);
	}

}