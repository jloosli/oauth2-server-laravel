<?php namespace Dingo\OAuth2\Storage\Fluent;

use Dingo\OAuth2\Storage\TokenInterface;
use Dingo\OAuth2\Entity\Token as TokenEntity;
use Dingo\OAuth2\Entity\Scope as ScopeEntity;

class Token extends Fluent implements TokenInterface {

	/**
	 * Insert a token into storage.
	 * 
	 * @param  string  $token
	 * @param  string  $type
	 * @param  string  $clientId
	 * @param  mixed  $userId
	 * @param  int  $expires
	 * @return \Dingo\OAuth2\Entity\Token|bool
	 */
	public function create($token, $type, $clientId, $userId, $expires)
	{
		$this->connection->table($this->tables['tokens'])->insert([
			'token'     => $token,
			'type'      => $type,
			'client_id' => $clientId,
			'user_id'   => $userId,
			'expires'   => date('Y-m-d H:i:s', $expires)
		]);

		return new TokenEntity($token, $type, $clientId, $userId, $expires);
	}

	/**
	 * Associate scopes with a token.
	 * 
	 * @param  string  $token
	 * @param  array  $scopes
	 * @return void
	 */
	public function associateScopes($token, array $scopes)
	{
		$batch = [];

		foreach ($scopes as $scope)
		{
			$batch[] = [
				'token' => $token,
				'scope' => $scope->getScope()
			];
		}

		$this->connection->table($this->tables['token_scopes'])->insert($batch);
	}

	/**
	 * Get an access token from storage.
	 * 
	 * @param  string  $token
	 * @return \Dingo\OAuth2\Entity\Token|bool
	 */
	public function get($token)
	{
		if (isset($this->cache[$token]))
		{
			return $this->cache[$token];
		}

		$query = $this->connection->table($this->tables['tokens'])->where('token', $token);

		if ( ! $token = $query->first())
		{
			return false;
		}

		return $this->cache[$token->token] = new TokenEntity($token->token, $token->type, $token->client_id, $token->user_id, strtotime($token->expires));;
	}

	/**
	 * Get an access token from storage with associated scopes.
	 * 
	 * @param  string  $token
	 * @return \Dingo\OAuth2\Entity\Token|bool
	 */
	public function getWithScopes($token)
	{
		if ( ! $token = $this->get($token))
		{
			return false;
		}

		// Now that the token has been fetched and the entity created we'll also fetch
		// the associated scopes of the token.
		$query = $this->connection->table($this->tables['scopes'])
								  ->select($this->tables['scopes'].'.*')
								  ->leftJoin($this->tables['token_scopes'], $this->tables['scopes'].'.scope', '=', $this->tables['token_scopes'].'.scope')
								  ->where($this->tables['token_scopes'].'.token', $token->getToken());

		$scopes = [];

		foreach ($query->get() as $scope)
		{
			$scopes[$scope->scope] = new ScopeEntity($scope->scope, $scope->name, $scope->description);
		}

		$token->attachScopes($scopes);

		return $this->cache[$token->getToken()] = $token;
	}

	/**
	 * Delete an access token from storage.
	 * 
	 * @param  string  $token
	 * @return void
	 */
	public function delete($token)
	{
		unset($this->cache[$token]);
		
		$this->connection->table($this->tables['tokens'])->where('token', $token)->delete();

		$this->connection->table($this->tables['token_scopes'])->where('token', $token)->delete();
	}

}