<?php namespace Dingo\OAuth2\Laravel\Console;

class UninstallCommand extends Command {

	/**
	 * Command name.
	 * 
	 * @var string
	 */
	protected $name = 'oauth:uninstall';

	/**
	 * Command description.
	 * 
	 * @var string
	 */
	protected $description = 'Run the OAuth 2.0 uninstaller';

	/**
	 * Fire the uninstall command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$connection = $this->getConnection();

		if ( ! $this->confirm('Are you sure you want to uninstall? This will delete any and all data and cannot be undone. (y/N)</question> ', false))
		{
			return;
		}
		
		$this->line('');

		$this->builder->on($connection)->down($this);

		$this->line('');

		$this->info('OAuth 2.0 package uninstalled successfully.');
	}

}