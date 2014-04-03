<?php
namespace LeagueWrap\Api;

use LeagueWrap\Response\Summoner;
use LeagueWrap\Region;
use LeagueWrap\Cache;
use LeagueWrap\CacheInterface;
use LeagueWrap\ClientInterface;
use LeagueWrap\Limit\Collection;
use LeagueWrap\Limit\LimitReachedException;

abstract class AbstractApi {
	
	/**
	 * The client used to communicate with the api.
	 *
	 * @var ClientInterface
	 */
	protected $client;

	/**
	 * The collection of limits to be used on this api.
	 *
	 * @var Collection
	 */
	protected $collection;

	/**
	 * The key to be used by the api.
	 *
	 * @param string
	 */
	protected $key;

	/**
	 * The region to be used by the api.
	 *
	 * @param string
	 */
	protected $region;

	/**
	 * A list of all permitted regions for this API call. Leave
	 * it empty to not lock out any region string.
	 *
	 * @param array
	 */
	protected $permittedRegions = [];

	/**
	 * The version we want to use. If null use the first
	 * version in the array.
	 *
	 * @param string|null
	 */
	protected $version = null;

	/**
	 * A count of the amount of API request this object has done
	 * so far.
	 *
	 * @param int
	 */
	protected $requests = 0;

	/**
	 * This is the cache container that we intend to use.
	 *
	 * @var CacheInterface
	 */
	protected $cache = null;

	/**
	 * The amount of time we intend to remember the response for.
	 *
	 * @var int
	 */
	protected $defaultRemember = 0;

	/**
	 * The amount of seconds to keep things in cache
	 *
	 * @var int
	 */
	protected $seconds = 0;

	/**
	 * Default DI constructor.
	 *
	 * @param ClientInterface $client
	 * @param Collection $collection
	 */
	public function __construct(ClientInterface $client, Collection $collection)
	{
		$this->client     = $client;
		$this->collection = $collection;
	}

	/**
	 * Returns the amount of requests this object has done
	 * to the api so far.
	 *
	 * @return int
	 */
	public function getRequestCount()
	{
		return $this->requests;
	}

	/**
	 * Set the key to be used in the api.
	 *
	 * @param string $key
	 * @chainable
	 */
	public function setKey($key)
	{
		$this->key = $key;
		return $this;
	}

	/**
	 * Set the region to be used in the api.
	 *
	 * @param string $region
	 * @chainable
	 */
	public function setRegion($region)
	{
		$this->region = strtolower($region);
		return $this;
	}

	/**
	 * Select the version of the api you wish to
	 * query.
	 *
	 * @param string $version
	 * @return bool|$this
	 * @chainable
	 */
	public function selectVersion($version)
	{
		if ( ! in_array($version, $this->versions))
		{
			// not a value version
			return false;
		}

		$this->version = $version;
		return $this;
	}

	/**
	 * Sets the amount of seconds we should remember the response for.
	 * Leave it empty (or null) if you want to use the default set for 
	 * each api request.
	 *
	 * @param int $seconds
	 * @param CacheInterface $cache
	 * @chainable
	 */
	public function remember($seconds = null, CacheInterface $cache = null)
	{
		if (is_null($cache))
		{
			// use the built in cache interface
			$cache = new Cache;
		}
		$this->cache = $cache;
		if (is_null($seconds))
		{
			$this->seconds = $this->defaultRemember;
		}
		else
		{
			$this->seconds = $seconds;
		}

		return $this;
	}

	/**
	 * Wraps the request of the api in this method.
	 *
	 * @param string $path
	 * @param array $params
	 * @return array
	 * @throws RegionException
	 * @throws LimitReachedException
	 */
	protected function Request($path, $params = [])
	{
		// get version
		$version = $this->getVersion();

		// get and validate the region
		$region = new Region($this->permittedRegions);
		if ($region->isLocked($this->region))
		{
			throw new RegionException('The region "'.$this->region.'" is not permited to query this API.');
		}

		// add the key to the param list
		$params['api_key'] = $this->key;

		$uri = $this->region.'/'.$version.'/'.$path;
		// check cache
		if ($this->cache instanceof CacheInterface)
		{
			$cacheKey = md5($uri.'?'.http_build_query($params));
			if ($this->cache->has($cacheKey))
			{
				$content = $this->cache->get($cacheKey);
			}
			else
			{
				// check if we have hit the limit
				if ( ! $this->collection->hitLimits())
				{
					throw new LimitReachedException('You have hit the request limit in your collection.');
				}
				$content = $this->client->request($uri, $params);

				// request was succesful
				++$this->requests;

				// we want to cache this response
				$this->cache->set($content, $cacheKey, $this->seconds);
			}
		}
		else
		{
			// check if we have hit the limit
			if ( ! $this->collection->hitLimits())
			{
				throw new LimitReachedException('You have hit the request limit in your collection.');
			}
			$content = $this->client->request($uri, $params);

			// request was succesful
			++$this->requests;
		}

		// decode the content
		return json_decode($content, true);
	}

	/**
	 * Get the version string.
	 *
	 * @return string
	 */
	protected function getVersion()
	{
		if (is_null($this->version))
		{
			// get the first version in versions
			$this->version = reset($this->versions);
		}
		
		return $this->version;
	}

	/**
	 * Attempts to extract an ID from the object/value given
	 *
	 * @param mixed $identity
	 * @return int
	 * @throws Exception
	 */
	protected function extractId($identity)
	{
		if ($identity instanceof Summoner)
		{
			return $identity->id;
		}
		elseif (is_int($identity))
		{
			return $identity;
		}
		else
		{
			throw new Exception('The identity given was not valid.');
		}
	}

	/**
	 * Attempts to extract an ID from the array given.
	 *
	 * @param mixed $identities
	 * @return array
	 * @uses extractId()
	 */
	protected function extractIds($identities)
	{
		$ids = [];
		if (is_array($identities))
		{
			foreach ($identities as $identity)
			{
				$ids[] = $this->extractId($identity);
			}
		}
		else
		{
			$ids[] = $this->extractId($identities);
		}

		return $ids;
	}

	/**
	 * Attempts to attach the response to a summoner object.
	 *
	 * @param mixed $identity
	 * @param mixed $response
	 * @param string $key
	 * @return bool
	 */
	protected function attachResponse($identity, $response, $key)
	{
		if ($identity instanceof Summoner)
		{
			$identity->set($key, $response);
			return true;
		}
		
		return false;
	}

	/**
	 * Attempts to attach all the responses to the correct summoner.
	 *
	 * @param mixed $identity
	 * @param mixed $responses
	 * @param string $key
	 * @return bool
	 */
	protected function attachResponses($identities, $responses, $key)
	{
		if (is_array($identities))
		{
			foreach ($identities as $identity)
			{
				if ($identity instanceof Summoner)
				{
					$id       = $identity->id;
					$response = $responses[$id];
					$this->attachResponse($identity, $response, $key);
				}
			}
		}
		else
		{
			$identity = $identities;
			if ($identity instanceof Summoner)
			{
				$id       = $identity->id;
				$response = $responses[$id];
				$this->attachResponse($identity, $response, $key);
			}
		}

		return true;
	}
}

