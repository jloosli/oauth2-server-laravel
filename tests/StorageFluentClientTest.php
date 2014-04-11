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
			'name' => 'test',
			'trusted' => false
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
			'redirect_uri' => null,
			'trusted' => false
		], $client->getAttributes());
	}


	public function testGetClientByIdPullsFromCacheOnSecondCall()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('clients.*')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('clients.id', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'trusted' => false
		]);

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('client_id', 'test')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('is_default', 1)->andReturn($builder);
		$builder->shouldReceive('pluck')->once()->with('uri')->andReturn(null);

		$storage->get('test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => null,
			'trusted' => false
		], $storage->get('test')->getAttributes());
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
			'name' => 'test',
			'trusted' => false
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
			'redirect_uri' => 'test',
			'trusted' => false
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
			'uri' => 'test',
			'trusted' => false
		]);

		$client = $storage->get('test', null, 'test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test',
			'trusted' => false
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
			'name' => 'test',
			'trusted' => false
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
			'redirect_uri' => null,
			'trusted' => false
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
			'uri' => 'test',
			'trusted' => false
		]);
		$client = $storage->get('test', 'test', 'test');

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test',
			'trusted' => false
		], $client->getAttributes());
	}


	public function testCreatingClientSucceedsAndReturnsClientEntity()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'id'     => 'test',
			'secret' => 'test',
			'name'   => 'test',
			'trusted' => false
		]);

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			[
				'client_id' => 'test',
				'uri' => 'test',
				'is_default'   => 1
			]
		]);

		$this->assertEquals([
			'id' => 'test',
			'secret' => 'test',
			'name' => 'test',
			'redirect_uri' => 'test',
			'trusted' => false
		], $storage->create('test', 'test', 'test', [['uri' => 'test', 'default' => true]])->getAttributes());
	}


	public function testDeletingClient()
	{
		$storage = new ClientStorage($this->db, ['clients' => 'clients', 'client_endpoints' => 'client_endpoints']);

		$this->db->shouldReceive('table')->once()->with('clients')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('id', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$this->db->shouldReceive('table')->once()->with('client_endpoints')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('client_id', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$storage->delete('test');
	}


	protected function getBuilderMock()
	{
		return m::mock('Illuminate\Database\Query\Builder');
	}


}