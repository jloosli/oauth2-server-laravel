<?php namespace Dingo\OAuth2;

use RuntimeException;
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

			// Set the access token and refresh token expirations on the server.
			$server->setAccessTokenExpiration($app['config']['oauth2-server::expirations.access']);

			$server->setRefreshTokenExpiration($app['config']['oauth2-server::expirations.refresh']);

			// Spin through each of the grants listed in the configuration file and
			// build an array of grants since some grants can be given options.
			foreach ($app['config']['oauth2-server::grants'] as $key => $value)
			{
				if ( ! is_string($key))
				{
					list ($key, $value) = [$value, []];
				}
				elseif ( ! is_array($value))
				{
					$value = [$value];
				}

				$grants[$key] = $value;
			}

			// We'll create an array of mappings to each of the grants class so that
			// users can use the shorthand name of the grant in the configuration
			// file.
			$mappings = [
				'password'      => 'Dingo\OAuth2\Grant\Password',
				'client'        => 'Dingo\OAuth2\Grant\ClientCredentials',
				'authorization' => 'Dingo\OAuth2\Grant\AuthorizationCode',
				'implicit'      => 'Dingo\OAuth2\Grant\Implicit',
				'refresh'       => 'Dingo\OAuth2\Grant\RefreshToken'
			];

			// Spin through each of the grants and if it isn't set in the mappings
			// then we'll error out. Otherwise we'll get an instance of the
			// grant and register it on the server.
			foreach ($grants as $grant => $options)
			{
				if ( ! isset($mappings[$grant]))
				{
					throw new RuntimeException("Supplied grant [{$grant}] is invalid.");
				}

				$instance = new $mappings[$grant];

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