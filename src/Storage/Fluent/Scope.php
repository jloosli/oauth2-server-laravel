<?php namespace Dingo\OAuth2\Storage\Fluent;

use Dingo\OAuth2\Storage\ScopeInterface;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;

class Scope extends Fluent implements ScopeInterface {

	/**
	 * Get a scope from storage.
	 * 
	 * @param  string  $scope
	 * @return \Dingo\OAuth2\Entity\Scope|false
	 */
	public function get($scope)
	{
		if (isset($this->cache[$scope]))
		{
			return $this->cache[$scope];
		}

		$query = $this->connection->table($this->tables['scopes'])->where('scope', $scope);

		if ( ! $scope = $query->first())
		{
			return false;
		}

		return $this->cache[$scope->scope] = new ScopeEntity($scope->scope, $scope->name, $scope->description);
	}

	/**
	 * Insert a scope into storage.
	 * 
	 * @param  string  $scope
	 * @param  string  $name
	 * @param  string  $description
	 * @return \Dingo\OAuth2\Entity\Scope|bool
	 */
	public function create($scope, $name, $description)
	{
		$this->connection->table($this->tables['scopes'])->insert([
			'scope'       => $scope,
			'name'        => $name,
			'description' => $description
		]);

		return new ScopeEntity($scope, $name, $description);
	}

	/**
	 * Delete a scope from storage.
	 * 
	 * @param  string  $scope
	 * @return void
	 */
	public function delete($scope)
	{
		unset($this->cache[$scope]);

		$this->connection->table($this->tables['scopes'])->where('scope', $scope)->delete();
	}

}