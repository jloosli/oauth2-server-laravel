<?php

return [

	/*
	|--------------------------------------------------------------------------
	| Grants
	|--------------------------------------------------------------------------
	|
	| You can register different grant types with the authorization server
	| to allow clients to authorize themselves.
	|
	*/

	'grants' => [

		'password' => [
			'uses' => 'Dingo\OAuth2\Grant\Password',
			'expirations' => [
				'access' => 604800
			],
			function ($identification, $password)
			{
				$credentials = ['email' => $identification, 'password' => $password];

				if ( ! Auth::once($credentials))
				{
					return false;
				}

				return Auth::user()->id;
			}
		]

	],

	/*
	|--------------------------------------------------------------------------
	| Storage Adapter
	|--------------------------------------------------------------------------
	|
	| The storage adapter is where the authorized tokens and clients are kept.
	|
	*/

	'storage' => function($app)
	{
		return new Dingo\OAuth2\Storage\FluentAdapter($app['db']->connection($app['config']['oauth2-server::connection']));
	},


	/*
	|--------------------------------------------------------------------------
	| Database Connection
	|--------------------------------------------------------------------------
	|
	| If using the Fluent storage adapter you can specify the connection to
	| use. If left blank it will use the default connection defined in
	| your "app/config/db.php" configuration file.
	|
	*/

	'connection' => ''

];