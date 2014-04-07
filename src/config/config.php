<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Grants
	|--------------------------------------------------------------------------
	|
	| You can register different grant types with the authorization server
	| to allow different forms of authorization.
	|
	| Supported: "password", "refresh", "authorization",
	|            "implicit", "client"
	|
	| The "password" grant must have a callback which is used to when
	| authenticating the resource owner. This callback should return
	| the authorized users ID if successful or false if it failed.
	|
	*/

	'grants' => [

		'refresh',
		'password' => function ($identification, $password)
		{
			$credentials = ['email' => $identification, 'password' => $password];

			if ( ! Auth::once($credentials))
			{
				return false;
			}

			return Auth::user()->id;
		}

	],

	/*
	|--------------------------------------------------------------------------
	| Expiration Times
	|--------------------------------------------------------------------------
	|
	| Both access and refresh tokens will expire after a given period of time.
	| By default an access token will expire after 1 hour and a refresh
	| token will expire after 7 days.
	|
	*/

	'expirations' => [

		'access' => 3600,

		'refresh' => 604800

	],

	/*
	|--------------------------------------------------------------------------
	| Storage Adapter
	|--------------------------------------------------------------------------
	|
	| You can configure the storage adapter used to store the related tokens,
	| clients, and authorization codes. By default the Fluent adapter is
	| used.
	|
	*/

	'storage' => function($app)
	{
		return new Dingo\OAuth2\Storage\FluentAdapter($app['db']->connection());
	}

];