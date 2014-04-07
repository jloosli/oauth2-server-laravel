## Laravel Wrapper for OAuth 2.0 Server

This is a wrapper for the [PHP OAuth 2.0 server](https://github.com/dingo/oauth2-server) package. This package allows easier integration for Laravel applications.

[![Build Status](https://travis-ci.org/dingo/oauth2-server-laravel.svg?branch=master)](https://travis-ci.org/dingo/oauth2-server-laravel)

## Installation

The package can be installed with Composer, either by modifying your `composer.json` directly or using the `composer require` command.

```
composer require dingo/oauth2-server-laravel:0.1.*
```

> Note that this package is still under development and has not been tagged as stable.

## Storage Adapters

This wrapper provides an additional storage adapter that integrates with Laravel's Fluent Query Builder.

- `Dingo\OAuth2\Storage\FluentAdapter`

This adapter is enabled by default in the configuration and will use your default connection from `app/config/database.php`.

## Usage Guide

See the [OAuth 2.0 package](https://github.com/dingo/oauth2-server) for a more detailed guide.

### Publishing Configuration

It's recommended that you publish the packages configuration so that you can make any changes.

```
artisan config:publish dingo/oauth2-server-laravel
```

The published configuration file will be available at `app/config/packages/dingo/oauth2-server-laravel/config.php`.

### Install and Uninstall Commands

You can use the `oauth:install` and `oauth:uninstall` commands to either create the required database tables or drop any existing database tables. The names of the tables created are defined in the configuration file.

Both commands will ask you what connection you would like to use. You can bypass this prompt by using the `--connection` option.

```
artisan oauth:install --connection mysql
```

### Automatic Dependency Resolution for Authorization and Resource Servers

When using Laravel's controllers you can let the IoC container automatically resolve the Authorization and Resource server instances by simply type-hinting.

```php
use Dingo\OAuth2\Server\Authorization;

class OAuthController extends Controller {

	protected $server;
	
	public function __construct(Authorization $server)
	{
		$this->server = $server;
	}

}
```

You do not need to worry about binding the class to the IoC container as the package handles it for you. Laravel will automatically resolve and inject an instance of `Dingo\OAuth2\Server\Authorization`. This also applies to `Dingo\OAuth2\Server\Resource`.