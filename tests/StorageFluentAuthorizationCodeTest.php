<?php

use Mockery as m;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;
use Dingo\OAuth2\Entity\AuthorizationCode as AuthorizationCodeEntity;
use Dingo\OAuth2\Storage\Fluent\AuthorizationCode as AuthorizationCodeStorage;

class StorageFluentAuthorizationCodeTest extends PHPUnit_Framework_TestCase {


	public function setUp()
	{
		$this->db = m::mock('Illuminate\Database\Connection');
	}


	public function tearDown()
	{
		m::close();
	}


	public function testCreateAuthorizationCodeEntityFailsAndReturnsFalse()
	{
		$storage = new AuthorizationCodeStorage($this->db, ['authorization_codes' => 'authorization_codes']);

		$this->db->shouldReceive('table')->once()->with('authorization_codes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'code'         => 'test',
			'client_id'    => 'test',
			'user_id'      => 1,
			'redirect_uri' => 'test',
			'expires'      => '1991-01-31 12:00:00'
		])->andReturn(false);

		$this->assertFalse($storage->create('test', 'test', 1, 'test', strtotime('31 January 1991 12:00:00')));
	}


	public function testCreateAuthorizationCodeEntitySucceedsAndReturnsAuthorizationCodeEntity()
	{
		$storage = new AuthorizationCodeStorage($this->db, ['authorization_codes' => 'authorization_codes']);

		$this->db->shouldReceive('table')->once()->with('authorization_codes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			'code'         => 'test',
			'client_id'    => 'test',
			'user_id'      => 1,
			'redirect_uri' => 'test',
			'expires'      => '1991-01-31 12:00:00'
		])->andReturn(true);

		$code = $storage->create('test', 'test', 1, 'test', strtotime('31 January 1991 12:00:00'));

		$this->assertEquals([
			'code' => 'test',
			'client_id' => 'test',
			'user_id' => 1,
			'redirect_uri' => 'test',
			'expires' => strtotime('1991-01-31 12:00:00'),
			'scopes' => []
		], $code->getAttributes());
	}


	public function testAssociatingScopesToAuthorizationCode()
	{
		$storage = new AuthorizationCodeStorage($this->db, ['authorization_code_scopes' => 'authorization_code_scopes']);

		$this->db->shouldReceive('table')->once()->with('authorization_code_scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('insert')->once()->with([
			[
				'code' => 'test',
				'scope' => 'foo'
			],
			[
				'code' => 'test',
				'scope' => 'bar'
			]
		])->andReturn(true);

		$storage->associateScopes('test', [
			'foo' => new ScopeEntity('foo', 'foo', 'foo'),
			'bar' => new ScopeEntity('bar', 'bar', 'bar')
		]);
	}


	public function testGetAuthorizationCodeEntityFailsAndReturnsFalse()
	{
		$storage = new AuthorizationCodeStorage($this->db, ['authorization_codes' => 'authorization_codes']);

		$this->db->shouldReceive('table')->once()->with('authorization_codes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('code', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn(null);

		$this->assertFalse($storage->get('test'));
	}


	public function testGetAuthorizationCodeEntitySucceedsAndReturnsAuthorizationCodeEntity()
	{
		$storage = new AuthorizationCodeStorage($this->db, [
			'authorization_codes' => 'authorization_codes',
			'scopes' => 'scopes',
			'authorization_code_scopes' => 'authorization_code_scopes'
		]);

		$this->db->shouldReceive('table')->once()->with('authorization_codes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('code', 'test')->andReturn($builder);
		$builder->shouldReceive('first')->once()->andReturn((object) [
			'code' => 'test',
			'client_id' => 'test',
			'user_id' => 1,
			'redirect_uri' => 'test',
			'expires' => '1991-01-31 12:00:00'
		]);

		$this->db->shouldReceive('table')->once()->with('scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('select')->once()->with('scopes.*')->andReturn($builder);
		$builder->shouldReceive('leftJoin')->once()->with('authorization_code_scopes', 'scopes.scope', '=', 'authorization_code_scopes.scope')->andReturn($builder);
		$builder->shouldReceive('where')->once()->with('authorization_code_scopes.code', 'test')->andReturn($builder);
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

		$code = $storage->get('test');

		$this->assertEquals([
			'code' => 'test',
			'client_id' => 'test',
			'user_id' => 1,
			'redirect_uri' => 'test',
			'scopes' => [
				'foo' => new ScopeEntity('foo', 'foo', 'foo'),
				'bar' => new ScopeEntity('bar', 'bar', 'bar')
			],
			'expires' => strtotime('1991-01-31 12:00:00')
		], $code->getAttributes());
	}


	public function testDeleteAuthorizationCode()
	{
		$storage = new AuthorizationCodeStorage($this->db, [
			'authorization_codes' => 'authorization_codes',
			'authorization_code_scopes' => 'authorization_code_scopes'
		]);

		$this->db->shouldReceive('table')->once()->with('authorization_codes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('code', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$this->db->shouldReceive('table')->once()->with('authorization_code_scopes')->andReturn($builder = $this->getBuilderMock());
		$builder->shouldReceive('where')->once()->with('code', 'test')->andReturn($builder);
		$builder->shouldReceive('delete')->once();

		$storage->delete('test');
	}


	protected function getBuilderMock()
	{
		return m::mock('Illuminate\Database\Query\Builder');
	}


}