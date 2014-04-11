<?php namespace Dingo\OAuth2\Laravel\Console;

use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand {

	/**
	 * Get the database connection.
	 * 
	 * @return \Illuminate\Database\Connection
	 */
	public function getConnection()
	{
		if ( ! $connection = $this->option('connection'))
		{
			$default = $this->laravel['config']->get('database.default');

			$connection = $this->ask("What database connection would you like to use? (default: {$default})</question> ", $default);
		}

		if ( ! array_key_exists($connection, $this->laravel['config']->get('database.connections')))
		{
			$this->error('Unable to use the given connection as it is not defined within the available connections.');

			exit;
		}

		return $this->laravel['db']->connection($connection);
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

	/**
	 * Insert a blank line into the output.
	 * 
	 * @return \Dingo\OAuth2\Laravel\Console\Command
	 */
	public function blankLine()
	{
		$this->line('');

		return $this;
	}

}