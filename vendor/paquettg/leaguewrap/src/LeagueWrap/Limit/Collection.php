<?php
namespace LeagueWrap\Limit;

use LeagueWrap\LimitInterface;

class Collection {

	protected $limits = [];

	public function addLimit(LimitInterface $limit)
	{
		$this->limits[] = $limit;
	}

	public function hitLimits($count = 1)
	{
		foreach ($this->limits as $limit)
		{
			if ( ! $limit->hit($count))
			{
				return false;
			}
		}

		return true;
	}

	public function remainingHits()
	{
		$remaining = null;
		foreach ($this->limits as $limit)
		{
			$hitsLeft = $limit->remaining();
			if (is_null($remaining) or
			    $hitsLeft < $remaining)
			{
				$remaining = $hitsLeft;
			}
		}

		return $remaining;
	}
}
