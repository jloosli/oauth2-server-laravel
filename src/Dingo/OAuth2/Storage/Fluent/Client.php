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

		// If no redirection URI was given then we'll set it to null so that we
		// can create a new Dingo\OAuth2\Entity\Client instance.
		if ( ! isset($client->uri))
		{
			$client->uri = null;
		}

		return new ClientEntity($client->id, $client->secret, $client->name, $client->uri);
	}
	
}