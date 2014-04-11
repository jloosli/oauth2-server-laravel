<?php namespace Dingo\OAuth2\Laravel\Console;

use Dingo\OAuth2\Laravel\TableBuilder;

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
	 * Table builder instance.
	 * 
	 * @var \Dingo\OAuth2\Laravel\TableBuilder
	 */
	protected $builder;

	/**
	 * Fire the install command.
	 * 
	 * @return void
	 */
	public function fire()
	{
		$connection = $this->getConnection();

		$this->blankLine();

		$this->builder->on($connection)->up($this);

		$this->blankLine();

		$this->info('OAuth 2.0 package installed successfully.');
	}

	/**
	 * Set the table builder instance.
	 * 
	 * @param  \Dingo\OAuth2\Laravel\TableBuilder  $builder
	 * @return \Dingo\OAuth2\Laravel\Console\InstallCommand
	 */
	public function setTableBuilder(TableBuilder $builder)
	{
		$this->builder = $builder;

		return $this;
	}

}