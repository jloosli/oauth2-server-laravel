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
		$this->creating($command, $this->tables['authorization_codes']);

		$this->createAuthorizationCodesTable();

		$this->done($command);

		// Create the authorization code scopes table.
		$this->creating($command, $this->tables['authorization_code_scopes']);
		
		$this->createAuthorizationCodeScopesTable();

		$this->done($command);

		// Create the clients table.
		$this->creating($command, $this->tables['clients']);

		$this->createClientsTable();

		$this->done($command);

		// Create the client endpoints table.
		$this->creating($command, $this->tables['client_endpoints']);

		$this->createClientEndpointsTable();

		$this->done($command);

		// Create the scopes table.
		$this->creating($command, $this->tables['scopes']);

		$this->createScopesTable();

		$this->done($command);

		// Create the tokens table.
		$this->creating($command, $this->tables['tokens']);

		$this->createTokensTable();

		$this->done($command);

		// Create the token scopes table.
		$this->creating($command, $this->tables['token_scopes']);

		$this->createTokenScopesTable();

		$this->done($command);
	}

	/**
	 * Destroy the tables.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @return void
	 */
	public function down(Command $command)
	{
		foreach ($this->tables as $table)
		{
			$this->dropping($command, $table);

			$this->schema->dropIfExists($table);

			$this->done($command);
		}
	}

	/**
	 * Write a creating message to the output.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @param  string  $table
	 * @return void
	 */
	protected function creating(Command $command, $table)
	{
		$command->getOutput()->write('Creating "'.$table.'" table... ');
	}

	/**
	 * Write a dropping message to the output.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @param  string  $table
	 * @return void
	 */
	protected function dropping(Command $command, $table)
	{
		$command->getOutput()->write('Dropping "'.$table.'" table... ');
	}

	/**
	 * Write a done message to the output.
	 * 
	 * @param  \Illuminate\Console\Command  $command
	 * @return void
	 */
	protected function done(Command $command)
	{
		$command->getOutput()->writeln('<info>Done!</info>');
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
			$table->string('redirect_uri')->nullable();
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
			$table->boolean('trusted');
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
			$table->string('user_id')->nullable();
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