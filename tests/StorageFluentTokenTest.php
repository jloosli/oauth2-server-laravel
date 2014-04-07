<?php

use Mockery as m;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;
use Dingo\OAuth2\Entity\AuthorizationCode as AuthorizationCodeEntity;
use Dingo\OAuth2\Storage\Fluent\Token as TokenStorage;

class StorageFluentTokenTest extends PHPUnit_Framework_TestCase {


	public function setUp()
	{
		$this->db = m::mock('Illuminate\Database\Connection');
	}


	public function tearDown()
	{
		m::close();
	}


	public function testCreateTokenEntityFailsAndReturnsFalse()
	{
		$storage = new TokenStorage($this->db, ['tokens' => 'tokens']);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'token'        => 'test',
			'type'         => 'access',
			'client_id'    => 'test',
			'user_id'      => 1,
			'expires'      => '1991-01-31 12:00:00'
		])->andReturn(false);

		$this->assertFalse($storage->create('test', 'access', 'test', 1, strtotime('31 January 1991 12:00:00')));
	}


	public function testCreateTokenEntitySucceedsAndReturnsTokenEntity()
	{
		$storage = new TokenStorage($this->db, ['tokens' => 'tokens']);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'token'        => 'test',
			'type'         => 'access',
			'client_id'    => 'test',
			'user_id'      => 1,
			'expires'      => '1991-01-31 12:00:00'
		])->andReturn(true);

		$token = $storage->create('test', 'access', 'test', 1, strtotime('31 January 1991 12:00:00'));

		$this->assertEquals([
			'token' => 'test',
			'type' => 'access',
			'client_id' => 'test',
			'user_id' => 1,
			'expires' => strtotime('1991-01-31 12:00:00'),
			'scopes' => []
		], $token->getAttributes());
	}


	public function testAssociatingScopesToToken()
	{
		$storage = new TokenStorage($this->db, ['token_scopes' => 'token_scopes']);

		$this->db->shouldReceive('table')->once()->with('token_scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			[
				'token' => 'test',
				'scope' => 'foo'
			],
			[
				'token' => 'test',
				'scope' => 'bar'
			]
		])->andReturn(true);

		$storage->associateScopes('test', [
			'foo' => new ScopeEntity('foo', 'foo', 'foo'),
			'bar' => new ScopeEntity('bar', 'bar', 'bar')
		]);
	}


	public function testGetTokenEntityFailsAndReturnsFalse()
	{
		$storage = new TokenStorage($this->db, ['tokens' => 'tokens']);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn(null);

		$this->assertFalse($storage->get('test'));
	}


	public function testGetTokenEntitySucceedsAndReturnsTokenEntity()
	{
		$storage = new TokenStorage($this->db, ['tokens' => 'tokens']);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'token' => 'test',
			'type' => 'access',
			'client_id' => 'test',
			'user_id' => 1,
			'expires' => '1991-01-31 12:00:00'
		]);

		$token = $storage->get('test');

		$this->assertEquals([
			'token' => 'test',
			'type' => 'access',
			'client_id' => 'test',
			'user_id' => 1,
			'scopes' => [],
			'expires' => strtotime('1991-01-31 12:00:00')
		], $token->getAttributes());
	}


	public function testGetTokenWithScopesFailsAndReturnsFalse()
	{
		$storage = new TokenStorage($this->db, ['tokens' => 'tokens']);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn(null);

		$this->assertFalse($storage->getWithScopes('test'));
	}


	public function testGetTokenWithScopesSucceedsAndReturnsTokenEntityWithAttachedScopes()
	{
		$storage = new TokenStorage($this->db, [
			'tokens' => 'tokens',
			'scopes' => 'scopes',
			'token_scopes' => 'token_scopes'
		]);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'token' => 'test',
			'type' => 'access',
			'client_id' => 'test',
			'user_id' => 1,
			'expires' => '1991-01-31 12:00:00'
		]);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('scopes.*')->andReturn($builder);
		$builder->shouldReceive('leftJoin')->once()->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('token_scopes.token', 'test')->andReturn($builder);
		$builder->shouldReceive('get')->once()->andReturn([
			(object) [
				'scope' => 'foo',
				'name' => 'foo',
				'description' => 'foo'
			],
			(object) [
				'scope' => 'bar',
				'name' => 'bar',
				'description' => 'bar'
			]
		]);

		$token = $storage->getWithScopes('test');

		$this->assertEquals([
			'token' => 'test',
			'type' => 'access',
			'client_id' => 'test',
			'user_id' => 1,
			'scopes' => [
				'foo' => new ScopeEntity('foo', 'foo', 'foo'),
				'bar' => new ScopeEntity('bar', 'bar', 'bar')
			],
			'expires' => strtotime('1991-01-31 12:00:00')
		], $token->getAttributes());
	}


	public function testDeleteToken()
	{
		$storage = new TokenStorage($this->db, [
			'tokens' => 'tokens',
			'token_scopes' => 'token_scopes'
		]);

		$this->db->shouldReceive('table')->once()->with('tokens')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$this->db->shouldReceive('table')->once()->with('token_scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('token', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$storage->delete('test');
	}


	protected function getBuilderMock()
	{
		return m::mock('Illuminate\Database\Query\Builder');
	}


}