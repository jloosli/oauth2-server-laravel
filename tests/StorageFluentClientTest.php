<?php

use Mockery as m;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;
use Dingo\OAuth2\Entity\AuthorizationCode as AuthorizationCodeEntity;
use Dingo\OAuth2\Storage\Fluent\Client as ClientStorage;

class StorageFluentClientTest extends PHPUnit_Framework_TestCase {


	public function setUp()
	{
		$this->db = m::mock('Illuminate\Database\Connection');
	}


	public function tearDown()
	{
		m::close();
	}


	public function testGetClientByIdFailsAndReturnsFalse()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn(null);

		$this->assertFalse($storage->get('test'));
	}


	public function testGetClientByIdSucceedsAndRedirectionUriIsNotFound()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test'
		]);

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('client_id', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('is_default', 1)->andReturn($builder);
		$builder->shouldReceive('pluck')->once()->with('uri')->andReturn(null);

		$client = $storage->get('test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => null
		], $client->getAttributes());
	}


	public function testGetClientByIdSucceedsAndRedirectionUriIsFound()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test'
		]);

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('client_id', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('is_default', 1)->andReturn($builder);
		$builder->shouldReceive('pluck')->once()->with('uri')->andReturn('test');

		$client = $storage->get('test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test'
		], $client->getAttributes());
	}


	public function testGetClientByIdAndRedirectionUriSucceeds()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('addSelect')->once()->with('client_endpoints.uri')->andReturn($builder);
		$builder->shouldReceive('join')->once()->with('client_endpoints', 'clients.id', '=', 'client_endpoints.client_id')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('client_endpoints.uri', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'uri' => 'test'
		]);

		$client = $storage->get('test', null, 'test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test'
		], $client->getAttributes());
	}


	public function testGetClientByIdAndSecretSucceeds()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.secret', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test'
		]);

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('client_id', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('is_default', 1)->andReturn($builder);
		$builder->shouldReceive('pluck')->once()->with('uri')->andReturn(null);

		$client = $storage->get('test', 'test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => null
		], $client->getAttributes());
	}


	public function testGetClientByIdAndSecretAndRedirectionUriSucceeds()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('addSelect')->once()->with('client_endpoints.uri')->andReturn($builder);
		$builder->shouldReceive('join')->once()->with('client_endpoints', 'clients.id', '=', 'client_endpoints.client_id')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('client_endpoints.uri', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.secret', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'uri' => 'test'
		]);
		$client = $storage->get('test', 'test', 'test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test'
		], $client->getAttributes());
	}


	protected function getBuilderMock()
	{
		return m::mock('Illuminate\Database\Query\Builder');
	}


}