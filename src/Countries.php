<?php namespace Countries;

use Respect\Validation\Validator as v;

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
	private $pref      = [
		'name',
		'iso2',
	];
	private $availPref = [
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
	 * @param array $pref
	 * @param bool  $strict
	 */
	public function __construct($pref = [], $strict = false)
	{
		$this->setPref($pref);
		v::type('bool')->assert($strict);

		$this->strict     = $strict;
		$this->isoDetails = include 'isoDetails.php';
	}

	/**
	 * Sets Preferences of what you want returned
	 *
	 * @param $pref
	 */
	public function setPref($pref)
	{
		if (v::arrayType()->assert($pref))
		{
			v::arrayVal()->each(v::in($this->availPref))->assert($pref);
			$this->pref = ($pref) ?: $this->availPref;
		}
	}

	/**
	 * Set the preferred sort order of returned countries
	 *
	 * @param $key
	 */
	public function setSort($key)
	{
		v::contains($key)->assert($this->availPref);

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

		if (v::stringType()->length(2, 3)->validate($term))
		{
			$results = $this->getCountryFromISO($term);
		}
		else
		{
			$results = $this->getCountryFromName($term);
		}

		if ($this->strict || $results)
		{
			return $this->formatResults($results);
		}

		if (v::not(v::intVal())->validate($term))
		{
			$results = $this->search($term);;

			return $this->formatResults($results);
		}

		return null;
	}

	/**
	 * Provide 2 or 3 letter ISO and get the country
	 *
	 * @param $term
	 *
	 * @return null|array
	 */
	public function getCountryFromISO($term)
	{
		v::stringType()->length(2, 3)->assert($term);

		if (v::intVal()->validate($term))
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

		return $this->formatResults($results);
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
		v::stringType()->notEmpty()->assert($term);

		$results = $this->findByKey('name', $term);

		return $this->formatResults($results);
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

		return $this->formatResults($results);
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

	/**
	 * @param $results
	 *
	 * @return null|array
	 */
	private function formatResults($results)
	{
		if ($results === null)
		{
			return null;
		}

		foreach ($results as $key => $item)
		{
			if (is_array($item))
			{
				$results[ $key ] = $this->formatResults($item);
			}
			elseif (!in_array($key, $this->pref))
			{
				unset($results[ $key ]);
			}
		}

		return $results;
	}
}