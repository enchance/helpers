<?php

return [
	
	'regex' => [
		'cleanup_number' => '/[\s()-\.+]+/',
		'name_prefix' => '/(de|delos|sta|sta\.|del|dela|san|los|la)/i',
		'name_suffix' => '/([ivx]*|jr|sr|ph\.d)\.?$/i',
	],

	/*
	|--------------------------------------------------------------------------
	| Tables
	|--------------------------------------------------------------------------
	|
	| Table names
	|
	*/
	'tables' => [
		'options'       => 'options',
		'users_options' => 'users_options',
		'dbcountry'     => 'dbcountry'
	],

];