<?php namespace Dingo\OAuth2\Laravel;

use Illuminate\Console\Command;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder as SchemaBuilder;

class TableBuilder {

	/**
	 * Schema builder instance.
	 * 
	 * @var \Illuminate\Database\Schema\Builder
	 */
	protected $schema;

	/**
	 * Array of tables.
	 * 
	 * @var array
	 */
	protected $tables;

	/**
	 * Create a new Dingo\OAuth2\Laravel\TableBuilder instance.
	 * 
	 * @param  \Illuminate\Database\Schema\Builder  $schema
	 * @param  array  $tables
	 * @return void
	 */
	public function __construct(SchemaBuilder $schema, array $tables)
	{
		$this->schema = $schema;
		$this->tables = $tables;
	}

	/**
	 * Set the connection to build the tables on.
	 * 
	 * @param  \Illuminate\Database\Connection  $connection
	 * @return \Dingo\OAuth2\Laravel\TableBuilder
	 */
	public function on(Connection $connection)
	{
		$this->schema->setConnection($connection);

		return $this;
	}

	/**
	 * Build the tables.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @return void
	 */
	public function up(Command $command)
	{
		// Create the authorization codes table.
		$command->getOutput()->write('Creating authorization codes table... ');

		$this->createAuthorizationCodesTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the authorization code scopes table.
		$command->getOutput()->write('Creating authorization code scopes table... ');
		
		$this->createAuthorizationCodeScopesTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the clients table.
		$command->getOutput()->write('Creating clients table... ');

		$this->createClientsTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the client endpoints table.
		$command->getOutput()->write('Creating client endpoints table... ');

		$this->createClientEndpointsTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the scopes table.
		$command->getOutput()->write('Creating scopes table... ');

		$this->createScopesTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the tokens table.
		$command->getOutput()->write('Creating tokens table... ');

		$this->createTokensTable();

		$command->getOutput()->writeln('<info>Done!</info>');

		// Create the token scopes table.
		$command->getOutput()->write('Creating token scopes table... ');

		$this->createTokenScopesTable();

		$command->getOutput()->writeln('<info>Done!</info>');
	}

	/**
	 * Destroy the tables.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @return void
	 */
	public function down(Command $command)
	{
		foreach ($this->tables as $key => $table)
		{
			$command->getOutput()->write('Dropping '.str_replace('_', ' ', $key).' table... ');

			$this->schema->dropIfExists($table);

			$command->getOutput()->writeln('<info>Done!</info>');
		}
	}

	/**
	 * Create the authorization codes table.
	 * 
	 * @return void
	 */
	protected function createAuthorizationCodesTable()
	{
		$this->schema->create($this->tables['authorization_codes'], function($table)
		{
			$table->string('code', 40)->primary();
			$table->string('client_id');
			$table->string('user_id');
			$table->string('redirect_uri');
			$table->dateTime('expires');
		});
	}

	/**
	 * Create the authorization code scopes table.
	 * 
	 * @return void
	 */
	protected function createAuthorizationCodeScopesTable()
	{
		$this->schema->create($this->tables['authorization_code_scopes'], function($table)
		{
			$table->increments('id');
			$table->string('code', 40)->index();
			$table->string('scope')->index();
		});
	}

	/**
	 * Create the clients table.
	 * 
	 * @return void
	 */
	protected function createClientsTable()
	{
		$this->schema->create($this->tables['clients'], function($table)
		{
			$table->string('id', 40)->primary();
			$table->string('secret', 40)->index();
			$table->string('name');
		});
	}

	/**
	 * Create the client endpoints table.
	 * 
	 * @return void
	 */
	protected function createClientEndpointsTable()
	{
		$this->schema->create($this->tables['client_endpoints'], function($table)
		{
			$table->increments('id');
			$table->string('client_id', 40)->index();
			$table->string('uri')->index();
			$table->boolean('is_default')->default(0);
		});
	}

	/**
	 * Create the scopes table.
	 * 
	 * @return void
	 */
	protected function createScopesTable()
	{
		$this->schema->create($this->tables['scopes'], function($table)
		{
			$table->string('scope')->primary();
			$table->string('name');
			$table->text('description');
		});
	}

	/**
	 * Create the tokens table.
	 * 
	 * @return void
	 */
	protected function createTokensTable()
	{
		$this->schema->create($this->tables['tokens'], function($table)
		{
			$table->string('token', 40)->primary();
			$table->enum('type', ['access', 'refresh'])->default('access');
			$table->string('client_id', 40);
			$table->string('user_id');
			$table->dateTime('expires');
		});
	}

	/**
	 * Create the token scopes table.
	 * 
	 * @return void
	 */
	protected function createTokenScopesTable()
	{
		$this->schema->create($this->tables['token_scopes'], function($table)
		{
			$table->increments('id');
			$table->string('token', 40)->index();
			$table->string('scope')->index();
		});
	}

}