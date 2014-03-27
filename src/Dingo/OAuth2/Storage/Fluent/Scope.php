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
		$query = $this->connection->table($this->tables['scopes'])->where('scope', $scope);

		if ( ! $scope = $query->first())
		{
			return false;
		}

		return new ScopeEntity($scope->scope, $scope->name, $scope->description);
	}

}