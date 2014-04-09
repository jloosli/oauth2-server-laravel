<?php

use Mockery as m;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;
use Dingo\OAuth2\Entity\AuthorizationCode as AuthorizationCodeEntity;
use Dingo\OAuth2\Storage\Fluent\Scope as ScopeStorage;

class StorageFluentScopeTest extends PHPUnit_Framework_TestCase {


	public function setUp()
	{
		$this->db = m::mock('Illuminate\Database\Connection');
	}


	public function tearDown()
	{
		m::close();
	}


	public function testGetScopeFailsAndReturnsFalse()
	{
		$storage = new ScopeStorage($this->db, ['scopes' => 'scopes']);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('scope', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn(null);

		$this->assertFalse($storage->get('test'));
	}


	public function testGetScopeSucceedsAndReturnsScopeEntity()
	{
		$storage = new ScopeStorage($this->db, ['scopes' => 'scopes']);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('scope', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		]);
		$scope = $storage->get('test');

		$this->assertEquals([
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		], $scope->getAttributes());
	}


	public function testGetScopePullsFromCacheOnSecondCall()
	{
		$storage = new ScopeStorage($this->db, ['scopes' => 'scopes']);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('scope', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		]);

		$storage->get('test');

		$this->assertEquals([
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		], $storage->get('test')->getAttributes());
	}


	public function testCreatingScopeSucceedsAndReturnsScopeEntity()
	{
		$storage = new ScopeStorage($this->db, ['scopes' => 'scopes']);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		]);

		$this->assertEquals([
			'scope' => 'test',
			'name' => 'test',
			'description' => 'test'
		], $storage->create('test', 'test', 'test')->getAttributes());
	}


	public function testDeletingScope()
	{
		$storage = new ScopeStorage($this->db, ['scopes' => 'scopes']);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('scope', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$storage->delete('test');
	}


	protected function getBuilderMock()
	{
		return m::mock('Illuminate\Database\Query\Builder');
	}


}