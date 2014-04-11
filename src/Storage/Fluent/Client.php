<?php namespace Dingo\OAuth2\Storage\Fluent;

use Dingo\OAuth2\Storage\ClientInterface;
use Dingo\OAuth2\Entity\Client as ClientEntity;

class Client extends Fluent implements ClientInterface {

	/**
	 * Get a client from storage.
	 * 
	 * @param  string  $id
	 * @param  string  $secret
	 * @param  string  $redirectUri
	 * @return \Dingo\OAuth2\Entity\Client|false
	 */
	public function get($id, $secret = null, $redirectUri = null)
	{
		if (isset($this->cache[$id]))
		{
			return $this->cache[$id];
		}

		$query = $this->connection->table($this->tables['clients'])->select($this->tables['clients'].'.*');

		// If a secret and redirection URI were given then we must correctly
		// validate the client by comparing its ID, secret, and that
		// the supplied redirection URI was registered.
		if ( ! is_null($secret) and ! is_null($redirectUri))
		{
			$query->addSelect($this->tables['client_endpoints'].'.uri')
				  ->join($this->tables['client_endpoints'], $this->tables['clients'].'.id', '=', $this->tables['client_endpoints'].'.client_id')
				  ->where($this->tables['clients'].'.secret', $secret)
				  ->where($this->tables['client_endpoints'].'.uri', $redirectUri);
		}

		// If only the clients secret is given then we must correctly validate
		// the client by comparing its ID and secret.
		elseif ( ! is_null($secret) and is_null($redirectUri))
		{
			$query->where($this->tables['clients'].'.secret', $secret);
		}

		// If only the clients redirection URI is given then we must correctly
		// validate the client by comparing its ID and the redirection URI.
		elseif (is_null($secret) and ! is_null($redirectUri))
		{
			$query->addSelect($this->tables['client_endpoints'].'.uri')
				  ->join($this->tables['client_endpoints'], $this->tables['clients'].'.id', '=', $this->tables['client_endpoints'].'.client_id')
				  ->where($this->tables['client_endpoints'].'.uri', $redirectUri);
		}

		$query->where($this->tables['clients'].'.id', $id);

		if ( ! $client = $query->first())
		{
			return false;
		}

		// If no redirection URI was given then we'll fetch one from storage so that
		// it can be included in the entity.
		if ( ! isset($client->uri))
		{
			$client->uri = $this->connection->table($this->tables['client_endpoints'])
								->where('client_id', $id)
								->where('is_default', 1)
								->pluck('uri');
		}

		return $this->cache[$client->id] = new ClientEntity($client->id, $client->secret, $client->name, (bool) $client->trusted, $client->uri);
	}

	/**
	 * Create a client and associated redirection URIs.
	 * 
	 * @param  string  $id
	 * @param  string  $secret
	 * @param  string  $name
	 * @param  array  $redirectUris
	 * @param  bool  $trusted
	 * @return \Dingo\OAuth2\Entity\Client|bool
	 */
	public function create($id, $secret, $name, array $redirectUris, $trusted = false)
	{
		$this->connection->table($this->tables['clients'])->insert([
			'id'      => $id,
			'secret'  => $secret,
			'name'    => $name,
			'trusted' => (int) $trusted
		]);

		$redirectUri = null;

		$batch = [];

		foreach ($redirectUris as $uri)
		{
			// If this redirection URI is the default then we'll set our redirection URI
			// to this URI for when we return the client entity.
			if ($uri['default'])
			{
				$redirectUri = $uri['uri'];
			}

			$batch[] = [
				'client_id' => $id,
				'uri' => $uri['uri'],
				'is_default' => (int) $uri['default']
			];
		}

		$this->connection->table($this->tables['client_endpoints'])->insert($batch);

		return new ClientEntity($id, $secret, $name, (bool) $trusted, $redirectUri);
	}

	/**
	 * Delete a client and associated redirection URIs.
	 * 
	 * @param  string  $id
	 * @return void
	 */
	public function delete($id)
	{
		unset($this->cache[$id]);

		$this->connection->table($this->tables['clients'])->where('id', $id)->delete();

		$this->connection->table($this->tables['client_endpoints'])->where('client_id', $id)->delete();
	}
	
}