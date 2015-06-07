<?php namespace Enchance\Helpers;

use Illuminate\Support\ServiceProvider;

class HelpersServiceProvider extends ServiceProvider {

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__.'/config/helpers.php' => config_path('helpers.php'),
		]);
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom( __DIR__.'/config/helpers.php', 'helpers');
		$this->app['helpers'] = $this->app->share(function($app) {
			return new Helpers;
		});
	}

}
