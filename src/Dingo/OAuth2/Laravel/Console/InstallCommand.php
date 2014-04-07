<?php namespace Dingo\OAuth2\Laravel\Console;

class InstallCommand extends Command {

	/**
	 * Command name.
	 * 
	 * @var string
	 */
	protected $name = 'oauth2:install';

	/**
	 * Command description.
	 * 
	 * @var string
	 */
	protected $description = 'Run the OAuth 2.0 package installer';

	/**
	 * Fire the install command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$connection = $this->getConnection();

		$this->builder->on($connection)->up($this);

		$this->line('');

		$this->info('OAuth 2.0 package installed successfully.');
	}

}