<?php namespace Dingo\OAuth2\Laravel\Console;

use Symfony\Component\Console\Input\InputOption;

class InstallCommand extends Command {

	/**
	 * Command name.
	 * 
	 * @var string
	 */
	protected $name = 'oauth:install';

	/**
	 * Command description.
	 * 
	 * @var string
	 */
	protected $description = 'Run the OAuth 2.0 installer';

	/**
	 * Fire the install command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$this->builder->on($this->getConnection())->up($this);

		$this->blankLine();

		$this->info('OAuth 2.0 package installed successfully.');
	}

	/**
	 * Get the console command options.
	 * 
	 * @return array
	 */
	public function getOptions()
	{
		return [
			['connection', null, InputOption::VALUE_REQUIRED, 'The database connection to be used by the installer.']
		];
	}

}