<?php
namespace LeagueWrap\Api;

use LeagueWrap\Response;
use LeagueWrap\Response\RunePage;
use LeagueWrap\Response\Rune;
use LeagueWrap\Response\MasteryPage;
use LeagueWrap\Response\Talent;

class Summoner extends AbstractApi {

	/**
	 * The summoners we have loaded.
	 *
	 * @var array
	 */
	protected $summoners = [];

	/**
	 * Valid version for this api call.
	 *
	 * @var array
	 */
	protected $versions = [
		'v1.3',
	];

	/**
	 * A list of all permitted regions for the Champion api call.
	 *
	 * @param array
	 */
	protected $permittedRegions = [
		'br',
		'eune',
		'euw',
		'lan',
		'las',
		'na',
		'oce',
	];

	/**
	 * The amount of time we intend to remember the response for.
	 *
	 * @var int
	 */
	protected $defaultRemember = 600;

	/**
	 * Attempt to get a summoner by key.
	 *
	 * @param string $key
	 * @return object|null
	 */
	public function __get($key)
	{
		return $this->get($key);
	}

	/**
	 * Attempt to get a summoner by key.
	 *
	 * @param string $key
	 * @return object|null
	 */
	public function get($key)
	{
		$key = strtolower($key);
		if (isset($this->summoners[$key]))
		{
			return $this->summoners[$key];
		}
		return null;
	}

	/**
	 * Gets the information about the user by the given identification.
     *
     * @param mixed $identities
     * @return Response\Summoner
	 */
	public function info($identities)
	{
		$ids   = [];
		$names = [];
		if (is_array($identities))
		{
			foreach ($identities as $identity)
			{
				if (is_int($identity))
				{
					// it's the id
					$ids[] = $identity;
				}
				else
				{
					// the summoner name
					$names[] = $identity;
				}
			}
		}
		else
		{
			if (is_int($identities))
			{
				// it's the id
				$ids[] = $identities;
			}
			else
			{
				// the summoner name
				$names[] = $identities;
			}
		}
		$summoners = [];
		if (count($ids) > 0)
		{
			// it's the id
			$ids = $this->infoById($ids);
			if ( ! is_array($ids))
			{
				$ids = [$ids->name => $ids];
			}
		}
		if (count($names) > 0)
		{
			// the summoner name
			$names = $this->infoByName($names);
			if ( ! is_array($names))
			{
				$names = [$names->name => $names];
			}
		}

		$summoners = array_merge($ids, $names);

		if (count($summoners) == 1)
		{
			return reset($summoners);
		}
		else
		{
			return $summoners;
		}

	}

	/**
	 * Attempts to get all information about this user. This method
	 * will make 3 requests!
	 *
	 * @param mixed $identities
	 * @return Response\Summoner;
	 */
	public function allInfo($identities)
	{
		$summoners = $this->info($identities);
		$this->runePages($summoners);
		$this->masteryPages($summoners);
		
		return $summoners;
	}

	/**
	 * Gets the name of each summoner from a list of ids.
	 *
	 * @param mixed $identities
	 * @return array
	 */
	public function name($identities)
	{
		$ids = $this->extractIds($identities);
		$ids = implode(',', $ids);
		
		$array = $this->request('summoner/'.$ids.'/name');
		$names = [];
		foreach ($array as $id => $name)
		{
			$names[$id] = $name;
		}

		return $names;
	}

	/**
	 * Gets all rune pages of the given user object or id.
	 *
	 * @param mixed $identities
	 * @return array
	 * @throws Exception
	 */
	public function runePages($identities)
	{
		$ids = $this->extractIds($identities);
		$ids = implode(',', $ids);

		$array     = $this->request('summoner/'.$ids.'/runes');
		$summoners = [];
		foreach ($array as $summonerId => $data)
		{
			$runePages = [];
			foreach ($data['pages'] as $info)
			{
				if ( ! isset($info['slots']))
				{
					// no runes in this page
					$info['slots'] = [];
				}

				$slots = $info['slots'];
				unset($info['slots']);

				$runePage = new RunePage($info);

				// set runes
				$runes = [];
				foreach ($slots as $slot)
				{
					$id         = $slot['runeSlotId'];
					$rune       = new Rune($slot['rune']);
					$runes[$id] = $rune;
				}
				$runePage->runes = $runes;
				$runePages[]     = $runePage;
			}
			$summoners[$summonerId] = $runePages;
		}

		$this->attachResponses($identities, $summoners, 'runePages');
		if (count($summoners) == 1)
		{
			$runePages = reset($summoners);
			return $runePages;
		}
		else
		{
			return $summoners;
		}
	}

	/**
	 * Gets all the mastery pages of the given user object or id.
	 *
	 * @param mixed $identities
	 * @return array
	 * @throws Exception
	 */
	public function masteryPages($identities)
	{
		$ids = $this->extractIds($identities);
		$ids = implode(',', $ids);

		$array     = $this->request('summoner/'.$ids.'/masteries');
		$summoners = [];
		foreach ($array as $summonerId => $data)
		{
			$masteryPages = [];
			foreach ($data['pages'] as $info)
			{
				if ( ! isset($info['talents']))
				{
					// seting the talents to an empty array
					$info['talents'] = [];
				}

				$talentsInfo = $info['talents'];
				unset($info['talents']);
				$masteryPage = new MasteryPage($info);
				// set masterys
				$talents = [];
				foreach ($talentsInfo as $talent)
				{
					$id           = $talent['id'];
					$talent       = new Talent($talent);
					$talents[$id] = $talent;
				}
				$masteryPage->talents = $talents;
				$masteryPages[]       = $masteryPage;
			}
			$summoners[$summonerId] = $masteryPages;
		}

		$this->attachResponses($identities, $summoners, 'masteryPages');
		if (count($summoners) == 1)
		{
			$masteryPages = reset($summoners);
			return $masteryPages;
		}
		else
		{
			return $summoners;
		}
	}

	/**
	 * Gets the information by the id of the summoner.
	 *
	 * @param array $ids
	 * @return Response\Summoner|Response\Summoner[];
	 * @throws ListMaxException
	 */
	protected function infoById($ids)
	{
		if (is_array($ids))
		{
			if (count($ids) > 40)
			{
				throw new ListMaxException('This request can only support a list of 40 elements, '.count($ids).' given.');
			}
			$ids = implode(',', $ids);
		}
		$array     = $this->request('summoner/'.$ids);
		$summoners = [];
		foreach ($array as $id => $info)
		{
			$summoner               = new Response\Summoner($info);
			$name                   = $summoner->name;
			$this->summoners[$name] = $summoner;
			$summoners[$name]       = $summoner;
		}

		if (count($summoners) == 1)
		{
			return reset($summoners);
		}
		else
		{
			return $summoners;
		}
	}

	/**
	 * Gets the information by the name of the summoner.
	 *
	 * @param mixed $name
	 * @return Response\Summoner|Response\Summoner[];
	 * @throws ListMaxException
	 */
	protected function infoByName($names)
	{
		if (is_array($names))
		{
			if (count($names) > 40)
			{
				throw new ListMaxException('this request can only support a list of 40 elements, '.count($ids).' given.');
			}
			$names = implode(',', $names);
		}

		// clean the name
		$names     = htmlspecialchars($names);
		$array     = $this->request('summoner/by-name/'.$names);
		$summoners = [];
		foreach ($array as $name => $info)
		{
			$summoner = new Response\Summoner($info);
			$this->summoners[$name] = $summoner;
			$summoners[$name] = $summoner;
		}
		
		if (count($summoners) == 1)
		{
			return reset($summoners);
		}
		else
		{
			return $summoners;
		}
	}
}