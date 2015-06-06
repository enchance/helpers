<?php namespace Enchance\Helpers;

class Helpers {

	public static function xxx() {
		$a = config('helpers.message') . ' -- ' . config('helpers.a');
		// $a = config_path();
		// $a = base_path();
		// $a = 'What now?';

		return $a;
		// return "Still doesn't work.";
	}

}