<?php namespace Dingo\OAuth2\Laravel\Console;

class UninstallCommand extends Command {

	/**
	 * Command name.
	 * 
	 * @var string
	 */
	protected $name = 'oauth2:uninstall';

	/**
	 * Command description.
	 * 
	 * @var string
	 */
	protected $description = 'Run the OAuth 2.0 package uninstaller';

	/**
	 * Fire the uninstall command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$connection = $this->getConnection();

		$this->builder->on($connection)->down($this);

		$this->line('');

		$this->info('OAuth 2.0 package uninstalled successfully.');
	}

}