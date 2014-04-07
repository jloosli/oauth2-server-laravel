<?php

use Mockery as m;
use Dingo\OAuth2\Storage\FluentAdapter;

class StorageFluentAdapterTest extends PHPUnit_Framework_TestCase {


	public function tearDown()
	{
		m::close();
	}


	public function testGetClientDriver()
	{
		$storage = new FluentAdapter(m::mock('Illuminate\Database\Connection'));

		$this->assertInstanceOf('Dingo\OAuth2\Storage\Fluent\Client', $storage->get('client'));
	}


	public function testGetScopeDriver()
	{
		$storage = new FluentAdapter(m::mock('Illuminate\Database\Connection'));

		$this->assertInstanceOf('Dingo\OAuth2\Storage\Fluent\Scope', $storage->get('scope'));
	}


	public function testGetAuthorizationCodeDriver()
	{
		$storage = new FluentAdapter(m::mock('Illuminate\Database\Connection'));

		$this->assertInstanceOf('Dingo\OAuth2\Storage\Fluent\AuthorizationCode', $storage->get('authorization'));
	}


	public function testGetTokenDriver()
	{
		$storage = new FluentAdapter(m::mock('Illuminate\Database\Connection'));

		$this->assertInstanceOf('Dingo\OAuth2\Storage\Fluent\Token', $storage->get('token'));
	}


}