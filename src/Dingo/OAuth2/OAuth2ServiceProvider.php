<?php namespace Dingo\OAuth2;

use RuntimeException;
use Illuminate\Http\Response;
use Dingo\OAuth2\Server\Resource;
use Dingo\OAuth2\Server\Authorization;
use Dingo\OAuth2\Laravel\TableBuilder;
use Dingo\OAuth2\Storage\FluentAdapter;
use Illuminate\Support\ServiceProvider;
use Dingo\OAuth2\Laravel\Console\InstallCommand;
use Dingo\OAuth2\Exception\InvalidTokenException;
use Dingo\OAuth2\Laravel\Console\UninstallCommand;

class OAuth2ServiceProvider extends ServiceProvider {

	/**
	 * Boot the service provider.
	 * 
	 * @return void
	 */
	public function boot()
	{
		$this->package('dingo/oauth2-server-laravel', 'oauth');

		$this->app['Dingo\OAuth2\Server\Authorization'] = function($app)
		{
			return $app['dingo.oauth.authorization'];
		};

		$this->app['Dingo\OAuth2\Server\Resource'] = function($app)
		{
			return $app['dingo.oauth.resource'];
		};

		// Register the "oauth" filter which is used to protect resources by
		// requiring a valid access token with sufficient scopes.
		$this->app['router']->filter('oauth', function($route, $request)
		{
			$scopes = func_num_args() > 2 ? array_slice(func_get_args(), 2) : [];

			try
			{
				$this->app['dingo.oauth.resource']->validateRequest($scopes);
			}
			catch (InvalidTokenException $exception)
			{
				return new Response($exception->getMessage(), $exception->getStatusCode());
			}
		});
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

		$this->registerCommands();
	}

	/**
	 * Register the authorization server.
	 * 
	 * @return void
	 */
	protected function registerAuthorizationServer()
	{
		$this->app['dingo.oauth.authorization'] = $this->app->share(function($app)
		{
			$server = new Authorization($app['dingo.oauth.storage'], $app['request']);

			// Set the access token and refresh token expirations on the server.
			$server->setAccessTokenExpiration($app['config']['oauth::expirations.access']);

			$server->setRefreshTokenExpiration($app['config']['oauth::expirations.refresh']);

			// Spin through each of the grants listed in the configuration file and
			// build an array of grants since some grants can be given options.
			foreach ($app['config']['oauth::grants'] as $key => $value)
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
		$this->app['dingo.oauth.resource'] = $this->app->share(function($app)
		{
			$server = new Resource($app['dingo.oauth.storage'], $app['request']);

			$server->setDefaultScopes($app['config']['oauth::scopes']);

			return $server;
		});
	}

	/**
	 * Register the storage.
	 * 
	 * @return void
	 */
	protected function registerStorage()
	{
		$this->app['dingo.oauth.storage'] = $this->app->share(function($app)
		{
			$storage = $app['config']['oauth::storage']($app);

			return $storage->setTables($app['config']['oauth::tables']);
		});
	}

	/**
	 * Register commands.
	 * 
	 * @return void
	 */
	protected function registerCommands()
	{
		$this->app['dingo.oauth.command.install'] = $this->app->share(function($app)
		{
			$builder = new TableBuilder($app['db']->getSchemaBuilder(), $app['config']['oauth::tables']);

			return new InstallCommand($builder);
		});

		$this->app['dingo.oauth.command.uninstall'] = $this->app->share(function($app)
		{
			$builder = new TableBuilder($app['db']->getSchemaBuilder(), $app['config']['oauth::tables']);

			return new UninstallCommand($builder);
		});

		$this->commands('dingo.oauth.command.install', 'dingo.oauth.command.uninstall');
	}

}