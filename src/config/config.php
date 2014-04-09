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
	| The "password" grant MUST have a callback which is used to when
	| authenticating the resource owner. This callback should return
	| the authorized users ID if successful or false if it failed.
	|
	| The "authorization" grant MAY have a callback which is used
	| when a user successfully authorizes a client.
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
	| Default Scopes
	|--------------------------------------------------------------------------
	|
	| If you have common scopes that are used for protecting all resources
	| you can avoid having to specify on the filter of each route by
	| making them the default scopes. Any protected resource will
	| require the access token to have these scopes.
	|
	*/

	'scopes' => [],

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
	},

	/*
	|--------------------------------------------------------------------------
	| Storage Tables
	|--------------------------------------------------------------------------
	|
	| You can optionally configure the tables used by the storage adapter so
	| they better fit with your overall database design. You only need to
	| change the value of each array member, DO NOT change the keys.
	|
	*/

	'tables' => [

		'clients'                   => 'oauth_clients',
		'client_endpoints'          => 'oauth_client_endpoints',
		'tokens'                    => 'oauth_tokens',
		'token_scopes'              => 'oauth_token_scopes',
		'authorization_codes'       => 'oauth_authorization_codes',
		'authorization_code_scopes' => 'oauth_authorization_code_scopes',
		'scopes'                    => 'oauth_scopes'

	],

	/*
	|--------------------------------------------------------------------------
	| Unauthorized Resource Request
	|--------------------------------------------------------------------------
	|
	| By default, when an unauthorized request is made to a protected resource
	| we'll return a generic response with the error message and the
	| appropriate HTTP status code (usually 401).
	|
	*/

	'unauthorized' => function($error, $statusCode)
	{
		return Response::make($error, $statusCode);
	}

];