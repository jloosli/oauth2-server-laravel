<?php namespace Dingo\OAuth2\Laravel\Console;

use Dingo\OAuth2\Token;
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
	protected $description = 'Create a new scope or client in storage';

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

		$this->blankLine();

		$this->{'create'.ucfirst($entity).'Entity'}();
	}

	/**
	 * Create a new scope entity.
	 * 
	 * @return void
	 */
	protected function createScopeEntity()
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

		$this->info('Scope created! JSON representation of new scope:');

		$this->blankLine();

		$this->line(json_encode($scope->getAttributes(), JSON_PRETTY_PRINT));

		$this->blankLine();
	}

	/**
	 * Create a new client entity.
	 * 
	 * @return void
	 */
	protected function createClientEntity()
	{
		$id = Token::make();

		$secret = Token::make();

		$redirectUris = [];

		$id = $this->ask('New client identifier? (default: '.$id.')</question> ', $id);

		$secret = $this->ask('New client secret? (default: '.$secret.')</question> ', $secret);

		$name = $this->ask('New client name?</question> ');

		$trusted = $this->confirm('Is this client trusted? (y/N)</question> ', false);

		$redirectUris[] = ['uri' => $this->ask('Default client redirect URI?</question> '), 'default' => true];

		if ( ! $id or ! $secret or ! $name or empty($redirectUris))
		{
			$this->blankLine();

			$this->error('One or more of the questions was not answered. Unable to make new client.');

			exit;
		}

		while ($this->confirm('Would you like to create another client redirect URI? (y/N)</question> ', false))
		{
			$redirectUris[] = ['uri' => $this->ask('Client redirect URI?</question> '), 'default' => false];
		}

		$this->blankLine();

		$client = $this->storage('client')->create($id, $secret, $name, $redirectUris, $trusted);

		$this->info('Client created! JSON representation of new client:');

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
			['entity', InputArgument::REQUIRED, 'Entity to create (scope or client).']
		];
	}

}