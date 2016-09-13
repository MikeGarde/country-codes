<?php namespace Countries;

/**
 * This package is licensed under GPL-3.0
 *
 * The foundation of this package is the country code data, this is owned by the
 * International Organization for Standardization (ISO). I've made additional
 * contributions in the form of territorial information and input correction.
 *
 * More information from the ISO and licencing information is available here.
 *
 * @link http://www.iso.org/iso/home/standards/country_codes.htm
 * @link http://www.iso.org/iso/home/store/licence_agreement.htm
 */
class Countries {

	private $sortOrder = 'name';
	private $availSort = [
		'name',
		'iso2',
		'iso3',
		'isoNum',
		'fips',
		'capital',
		'isEU',
		'isUK',
		'isUS',
	];
	private $strict    = false;
	private $isoDetails;
	private $altSpellings;

	/**
	 * Countries constructor.
	 *
	 * @param bool $strict
	 *
	 * @throws \Exception
	 */
	public function __construct($strict = false)
	{
		if (!is_bool($strict))
		{
			throw new \Exception('strict must be boolean');
		}

		$this->strict     = $strict;
		$this->isoDetails = include 'isoDetails.php';
	}

	/**
	 * Set the preferred sort order of returned countries
	 *
	 * @param $key
	 *
	 * @throws \Exception
	 */
	public function setSort($key)
	{
		if (!in_array($key, $this->availSort))
		{
			throw new \Exception('sort must be one of the following: ' . implode(',', $this->availSort));
		}

		$this->sortOrder = $key;
	}

	/**
	 * Broad but comprehensive search
	 *
	 * @param $term
	 *
	 * @return null|array
	 */
	public function getCountry($term)
	{
		$results = null;

		if (preg_match('/^[a-z]{2,3}$/i', $term))
		{
			$results = $this->getCountryFromISO($term);
		}
		else
		{
			$results = $this->getCountryFromName($term);
		}

		if ($this->strict || $results)
		{
			return $results;
		}

		if (!is_int($term))
		{
			$results = $this->search($term);;

			return $results;
		}

		return null;
	}

	/**
	 * Provide 2 or 3 letter ISO and get the country
	 *
	 * @param $term
	 *
	 * @return null|array
	 * @throws \Exception
	 */
	public function getCountryFromISO($term)
	{
		if (!preg_match('/^[a-z0-9]{2,3}$/i', $term))
		{
			throw new \Exception('Must use 2-3 letter country code');
		}

		if (is_int($term))
		{
			$results = $this->findByKey('isoNum', $term);
		}
		elseif (strlen($term) == 2)
		{
			$results = $this->findByKey('iso2', $term);
		}
		else
		{
			$results = $this->findByKey('iso3', $term);
		}

		return $results;
	}

	/**
	 * Provide a name (must be an exact match)
	 *
	 * @param $term
	 *
	 * @return null|array
	 */
	public function getCountryFromName($term)
	{
		if ($term)
		{
			$results = $this->findByKey('name', $term);
		}
		else
		{
			$results = null;
		}

		return $results;
	}

	private function getNameFromISO($iso)
	{
	}

	private function getISOFromName($name)
	{
	}

	/**
	 * All countries
	 *
	 * @return array
	 */
	public function getAllCountries()
	{
		$results = $this->sort($this->isoDetails);

		return $results;
	}

	/**
	 * Get just the names of all countries
	 *
	 * @return array
	 */
	public function getAllCountryNames()
	{
		$results = [];

		foreach ($this->isoDetails as $country)
		{
			$results[] = $country['name'];
		}

		asort($results);
		$results = $this->stripKey($results);

		return $results;
	}

	/**
	 * @param $term
	 *
	 * @return bool
	 */
	public function valid($term)
	{
		return ($this->getCountry($term)) ? true : false;
	}

	/**
	 * @param $expected
	 * @param $term
	 *
	 * @return bool
	 */
	public function validate($expected, $term)
	{
		if (!preg_match('/^[a-z0-9]{2,3}$/i', $expected))
		{
			return false;
		}

		$key    = 'iso' . strlen($expected);
		$result = $this->getCountry($term);

		return ($expected == $result[ $key ]) ? true : false;
	}

	/**
	 * @param $expected
	 * @param $term
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function assert($expected, $term)
	{
		$exceptionMsg = sprintf('"%s" is not a valid country within ISO 3166-1', $term);

		if (!preg_match('/^[a-z0-9]{2,3}$/i', $expected))
		{
			throw new \Exception($exceptionMsg);
		}

		$key    = 'iso' . strlen($expected);
		$result = $this->getCountry($term);

		// Utilizes Respect/Validation for consistency is thrown errors
		if ($expected != $result[ $key ])
		{
			throw new \Exception($exceptionMsg);
		}

		return true;
	}

	/**
	 * @param $term
	 *
	 * @return bool
	 * @throws \Exception
	 */
	public function assertValid($term)
	{
		// Utilizes Respect/Validation for consistency is thrown errors
		if (!$this->getCountry($term))
		{
			throw new \Exception(sprintf('"%s" is not a valid country within ISO 3166-1', $term));
		}

		return true;
	}

	/**
	 * Is the country actually a US territory
	 *
	 * @param $term
	 *
	 * @return bool
	 */
	public function isUSTerritory($term)
	{
		$result = $this->getCountry($term);

		return ($result['isUS']) ? true : false;
	}

	/**
	 * Internal search for when exact matches were not found
	 *
	 * @param $term
	 *
	 * @return null
	 */
	private function search($term)
	{
		$term = strtolower($term);
		$term = preg_replace('/[^a-z ]+/', '', $term);

		/*
		 * Do we already know of an alternative spelling?
		 */
		$altSpellings = include 'altSpellings.php';

		foreach ($altSpellings as $alt => $iso)
		{
			if ($term == $alt)
			{
				return $this->getCountryFromISO($iso);
			}
		}


		/*
		 * Final attempt, Levenshtein math it
		 */
		if (strlen($term) == 2)
		{
			$key = 'iso2';
		}
		elseif (strlen($term) == 3)
		{
			$key = 'iso3';
		}
		else
		{
			$key = 'name';
		}
		$lowest  = null;
		$results = null;

		foreach ($this->isoDetails as $country)
		{
			$haystack = strtolower($country[ $key ]);
			$cost     = levenshtein($haystack, $term, 10, 9, 11);

			if ($cost < $lowest || $lowest === null)
			{
				$lowest  = $cost;
				$results = $country;
			}
		}

		/*
		 * Decide if we have respectable results
		 */
		similar_text($term, $results[ $key ], $percent);
		if ($percent < 20)
		{
			return null;
		}
		if (preg_match("/$term/i", $results[ $key ]))
		{
			return $results;
		}
		if (preg_match("/$results[$key]/i", $term))
		{
			return $results;
		}
		if (($lowest / strlen($results[ $key ])) >= 6)
		{
			return null;
		}

		return $results;
	}

	/**
	 * @param $key  Key to search
	 * @param $term Term to search for
	 *
	 * @return null
	 */
	private function findByKey($key, $term)
	{
		foreach ($this->isoDetails as $details)
		{
			if ($details[ $key ] == $term)
			{
				return $details;
			}
		}

		return null;
	}

	/**
	 * Sort an array by previously set key
	 *
	 * @param $array
	 *
	 * @return array
	 */
	private function sort($array)
	{
		$return = [];
		$key    = $this->sortOrder;

		foreach ($array as $item)
		{
			$return[ $item[ $key ] ] = $item;
		}

		ksort($return);

		return $this->stripKey($return);
	}

	/**
	 * Strips keys from the array so that it is presented with ascending keys matching the order it is returned in
	 *
	 * @param $array
	 *
	 * @return array
	 */
	private function stripKey($array)
	{
		$return = [];

		foreach ($array as $item)
		{
			$return[] = $item;
		}

		return $return;
	}
}