<?php namespace Dingo\OAuth2\Laravel\Console;

use Dingo\OAuth2\Laravel\TableBuilder;
use Symfony\Component\Console\Input\InputOption;
use Illuminate\Console\Command as IlluminateCommand;

class Command extends IlluminateCommand {

	/**
	 * Table builder instance.
	 * 
	 * @var \Dingo\OAuth2\Laravel\TableBuilder
	 */
	protected $builder;

	/**
	 * Create a new Dingo\OAuth2\Laravel\Console\UninstallCommand instance.
	 * 
	 * @param  \Dingo\OAuth2\Laravel\TableBuilder  $builder
	 * @return void
	 */
	public function __construct(TableBuilder $builder)
	{
		$this->builder = $builder;

		parent::__construct();
	}

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

}