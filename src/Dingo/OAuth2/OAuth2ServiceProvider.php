<?php namespace Dingo\OAuth2;

use Dingo\OAuth2\Server\Resource;
use Dingo\OAuth2\Server\Authorization;
use Dingo\OAuth2\Storage\FluentAdapter;
use Illuminate\Support\ServiceProvider;

class OAuth2ServiceProvider extends ServiceProvider {

	/**
	 * Boot the service provider.
	 * 
	 * @return void
	 */
	public function boot()
	{
		$this->package('dingo/oauth2-server-laravel', 'oauth2-server');
	}

	/**
	 * Register the service provider.
	 * 
	 * @return void
	 */
	public function register()
	{
		$this->registerAuthorizationServer();

		$this->registerResourceServer();

		$this->registerStorage();
	}

	/**
	 * Register the authorization server.
	 * 
	 * @return void
	 */
	protected function registerAuthorizationServer()
	{
		$this->app['dingo.oauth2.authorization'] = $this->app->share(function($app)
		{
			$server = new Authorization($app['dingo.oauth2.storage'], $app['request']);

			// Spin through each of the grants listed in the configuration file and
			// register them with the authorization server.
			foreach ($app['config']['oauth2-server::grants'] as $grant => $options)
			{
				$instance = new $options['uses'];

				if ($grant == 'password')
				{
					$instance->setAuthenticationCallback(array_pop($options));
				}

				$server->registerGrant($instance);
			}

			return $server;
		});
	}

	/**
	 * Register the resource server.
	 * 
	 * @return void
	 */
	protected function registerResourceServer()
	{
		$this->app['dingo.oauth2.resource'] = $this->app->share(function($app)
		{
			return new Resource($app['dingo.oauth2.storage'], $app['request']);
		});
	}

	/**
	 * Register the storage.
	 * 
	 * @return void
	 */
	protected function registerStorage()
	{
		$this->app['dingo.oauth2.storage'] = $this->app->share(function($app)
		{
			return $app['config']['oauth2-server::storage']($app);
		});
	}

}