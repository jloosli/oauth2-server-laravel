<?php namespace Dingo\OAuth2\Laravel\Console;

use Dingo\OAuth2\Token;
use Dingo\OAuth2\Storage\Adapter;
use Symfony\Component\Console\Input\InputArgument;

class NewCommand extends Command {

	/**
	 * Command name.
	 * 
	 * @var string
	 */
	protected $name = 'oauth:new';

	/**
	 * Command description.
	 * 
	 * @var string
	 */
	protected $description = 'Create a new "scope" or "client" entity';

	/**
	 * Storage adapter instance.
	 * 
	 * @var \Dingo\OAuth2\Storage\Adapter
	 */
	protected $storage;

	/**
	 * Fire the install command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$entity = $this->argument('entity');

		if ( ! in_array($entity, ['scope', 'client']))
		{
			$this->error('Unable to create unknown entity "'.$entity.'", available entities "scope" or "client".');

			exit;
		}

		$connection = $this->getConnection();

		$this->storage->setConnection($connection);

		$this->{'create'.ucfirst($entity).'Entity'}($connection);
	}

	/**
	 * Create a new scope entity.
	 * 
	 * @param  \Illuminate\Database\Connection  $connection
	 * @return void
	 */
	protected function createScopeEntity($connection)
	{
		$scope = $this->ask('New scope identifier?</question> ');

		$name = $this->ask('New scope name?</question> ');

		$description = $this->ask('New scope description?</question> ');

		$this->blankLine();

		if ( ! $scope or ! $name or ! $description)
		{
			$this->error('One or more of the questions was not answered. Unable to make new scope.');

			exit;
		}

		$scope = $this->storage('scope')->create($scope, $name, $description);

		$this->info('Scope saved! JSON representation of new scope:');

		$this->blankLine();

		$this->line(json_encode($scope->getAttributes(), JSON_PRETTY_PRINT));

		$this->blankLine();
	}

	/**
	 * Create a new client entity.
	 * 
	 * @param  \Illuminate\Database\Connection  $connection
	 * @return void
	 */
	protected function createClientEntity($connection)
	{
		$this->storage->setConnection($connection);

		// Create some default values for the client ID and the client secret,
		// we'll also create a blank array for the endpoints.
		$id = Token::make();

		$secret = Token::make();

		$endpoints = [];

		$id = $this->ask('New client identifier? (default: '.$id.')</question> ', $id);

		$secret = $this->ask('New client secret? (default: '.$secret.')</question> ', $secret);

		$name = $this->ask('New client name?</question> ');

		$trusted = $this->confirm('Is this client trusted? (y/N)</question> ', false);

		$endpoints[] = ['uri' => $this->ask('Default client endpoint?</question> '), 'default' => true];

		if ( ! $id or ! $secret or ! $name or empty($endpoints))
		{
			$this->blankLine();

			$this->error('One or more of the questions was not answered. Unable to make new client.');

			exit;
		}

		while ($this->confirm('Would you like to create another client endpoint? (y/N)</question> ', false))
		{
			$endpoints[] = ['uri' => $this->ask('Client endpoint?</question> '), 'default' => false];
		}

		$this->blankLine();

		$client = $this->storage('client')->create($id, $secret, $name, $endpoints, $trusted);

		$this->info('Client saved! JSON representation of new client:');

		$this->blankLine();

		$this->line(json_encode($client->getAttributes(), JSON_PRETTY_PRINT));

		$this->blankLine();
	}

	/**
	 * Get the command arguments.
	 * 
	 * @return array
	 */
	public function getArguments()
	{
		return [
			['entity', InputArgument::REQUIRED, 'New entity to make ("scope" or "client")']
		];
	}

	/**
	 * Get a storage from the storage adapter.
	 * 
	 * @param  string  $storage
	 * @return mixed
	 */
	protected function storage($storage)
	{
		return $this->storage->get($storage);
	}

	/**
	 * Set the storage adapter instance.
	 * 
	 * @param  \Dingo\OAuth2\Storage\Adapter  $storage
	 * @return \Dingo\OAuth2\Laravel\Console\NewCommand
	 */
	public function setStorage(Adapter $storage)
	{
		$this->storage = $storage;

		return $this;
	}

}