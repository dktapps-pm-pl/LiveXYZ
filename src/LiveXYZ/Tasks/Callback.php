<?php

namespace LiveXYZ\Tasks;

use pocketmine\scheduler\Task;

//Patch for PocketMine-MP which does not have the CallbackTask class.
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