
# Helper Package

A collection of methods I use for every project. This package also makes use of some helpers found in the [Cartalyst/Sentry](https://cartalyst.com/manual/sentry/2.1) package so check the docs below to see if the method in question has any required packages.

## Installation
In your `composer.json` file:

	"require": {
		"enchance/helpers": "dev-master"
	}

In `config/app.php`
	
	'providers' => [
		'Enchance\Helpers\HelpersServiceProvider',
	]

	'aliases' => [
		'H' => 'Enchance\Helpers\Facade\Helpers',
	]

## Usage
Use as intended.

## Methods

#### body_class()
A collection of classes for the `<body class="">` tag which helps in styling the page. This idea was taken from WordPress and customized for Laravel. <br />
__Requires:__ [Cartalyst/Sentry](https://cartalyst.com/manual/sentry/2.1)