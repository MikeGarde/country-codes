<?php namespace Country;

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
class Country {

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
	private $strict;
	private $isoDetails;

	public function __construct($pref = [], $strict = false)
	{
		if (v::arrayType()->assert($pref))
		{
			v::arrayVal()->each(v::in($this->availPref))->assert($pref);
			$this->pref = $pref;
		}

		v::type('bool')->assert($strict);

		$this->strict     = $strict;
		$this->isoDetails = include 'isoDetails.php';
	}

	public function setSort($key)
	{
		v::contains($key)->assert($this->availPref);

		$this->sortOrder = $key;
	}

	public function getCountry($term)
	{
		$return = null;

		if (v::stringType()->length(2, 3)->validate($term))
		{
			$return = $this->getCountryFromISO($term);
		}
		else
		{
			$return = $this->getCountryFromName($term);
		}

		if ($this->strict || $return)
		{
			return $return;
		}

		if (v::not(v::intVal())->validate($term))
		{
			return $this->search($term);
		}

		return null;
	}

	public function getCountryFromISO($term)
	{
		v::stringType()->length(2, 3)->assert($term);

		if (v::intVal()->validate($term))
		{
			return $this->findByKey('isoNum', $term);
		}
		elseif (strlen($term) == 2)
		{
			return $this->findByKey('iso2', $term);
		}
		else
		{
			return $this->findByKey('iso3', $term);
		}
	}

	public function getCountryFromName($term)
	{
		v::stringType()->notEmpty()->assert($term);

		return $this->findByKey('name', $term);
	}

	public function getNameFromISO($iso)
	{
	}

	public function getISOFromName($name)
	{
	}

	public function getAllCountries()
	{
		$return = $this->sort($this->isoDetails);

		return $return;
	}

	public function getAllCountryNames()
	{
		$return = [];

		foreach ($this->isoDetails as $country)
		{
			$return[] = $country['name'];
		}

		asort($return);

		return $this->stripKey($return);
	}

	/**
	 * @param $term
	 *
	 * @return null
	 */
	private function search($term)
	{
		$term   = strtolower($term);
		$term = preg_replace('/[^a-z ]+/', '', $term);

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
		 * Final attempt, levenshtein math it
		 */
		$lowest = null;
		$return = null;

		foreach ($this->isoDetails as $country)
		{
			$haystack = strtolower($country[ $key ]);
			$cost     = levenshtein($haystack, $term, 10, 9, 11);

			if ($cost < $lowest || $lowest === null)
			{
				$lowest = $cost;
				$return = $country;
			}
		}

		return $return;
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