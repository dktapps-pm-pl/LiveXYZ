# LiveXYZ
A plugin for PocketMine-MP to show your coordinates real-time as you move.

## NOTE
This plugin has been tested only on [PocketMine-MP](https://github.com/pmmp/PocketMine-MP). Compatibility with third party modified versions can sometimes be found, but I provide no support whatsoever for use with a non-official variant of PocketMine-MP.

- Current version: 0.6.0.3-beta
- Latest supported API version: 3.0.0-ALPHA7, 3.0.0-ALPHA8 @ https://github.com/pmmp/PocketMine-MP/commit/5190d9c1e28411e8de8bf77d769155b706d258d4

## Installation


1. Go to the [LiveXYZ Release Page](https://github.com/dktapps/LiveXYZ/releases) and download a PHAR file from there.
2. Copy the downloaded PHAR to the `plugins` folder in your server folder.
3. If you're making a fresh installation, you can simply reload the server. If you're updating the plugin, you'll need to shutdown and restart the server for changes to be detected.

## Usage
- To turn LiveXYZ on:
  As a player, type `/xyz` into the chat. (You can't currently do this for somebody else or from the console). Your coordinates will be shown above the hotbar.
- To turn LiveXYZ off:
  Simply type `/xyz` again to toggle the display.

## Permission nodes:
- `livexyz`: TRUE by default. Allows players with this permission to use LiveXYZ

### Copyright and licensing information
```
LiveXYZ - a PocketMine-MP plugin to show your coordinates real-time as you move
Copyright (C) 2016, 2017 Dylan K. Taylor

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
```
