<?php

namespace Countries;

use Exception;

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
class Countries
{
	private string $sortOrder = 'name';
	private array  $availSort = [
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
	private bool   $strict;
	private array  $isoDetails;
	private array  $altSpellings;

	/**
	 * Countries constructor.
	 *
	 * @param bool $strict
	 */
	public function __construct(bool $strict = false)
	{
		$this->strict = $strict;
		$countries    = json_decode(file_get_contents(__DIR__ . '/data/3166_1.json'), true);

		foreach ($countries as $country) {
			$this->isoDetails[] = new Country($country);
		}
	}

	/**
	 * Set the preferred sort order of returned countries
	 *
	 * @param $key
	 *
	 * @return Countries
	 * @throws Exception
	 */
	public function setSort($key): Countries
	{
		if (!in_array($key, $this->availSort)) {
			throw new Exception('sort must be one of the following: ' . implode(',', $this->availSort));
		}

		$this->sortOrder = $key;

		return $this;
	}

	/**
	 * Broad but comprehensive search
	 *
	 * @param $term
	 *
	 * @return ?Country
	 * @throws Exception
	 */
	public function getCountry($term): ?Country
	{
		if (preg_match('/^[a-z]{2,3}$/i', $term)) {
			$result = $this->getCountryFromISO($term);
		} else {
			$result = $this->getCountryFromName($term);
		}

		if ($this->strict || $result) {
			return $result;
		}

		if (!is_int($term)) {
			return $this->search($term);
		}

		return null;
	}

	/**
	 * Provide 2 or 3 letter ISO and get the country
	 *
	 * @param string|int $term
	 *
	 * @return ?Country
	 * @throws Exception
	 */
	public function getCountryFromISO($term): ?Country
	{
		if (!preg_match('/^[a-z0-9]{2,3}$/i', $term)) {
			throw new Exception('Must use 2-3 letter country code');
		}

		if (is_numeric($term)) {
			$term    = str_pad($term, 3, "0", STR_PAD_LEFT);
			$results = $this->findByKey('isoNum', $term);
		} elseif (strlen($term) == 2) {
			$results = $this->findByKey('iso2', $term);
		} else {
			$results = $this->findByKey('iso3', $term);
		}

		return $results;
	}

	/**
	 * Provide a name (must be an exact match)
	 *
	 * @param $term
	 *
	 * @return ?Country
	 * @throws Exception
	 */
	public function getCountryFromName($term): ?Country
	{
		if ($term) {
			$results = $this->findByKey('name', $term);
		} else {
			$results = null;
		}

		return $results;
	}

	//    private function getNameFromISO($iso)
	//    {
	//    }
	//
	//    private function getISOFromName($name)
	//    {
	//    }

	/**
	 * All countries
	 *
	 * @return array
	 */
	public function getAllCountries(): array
	{
		return $this->sort($this->isoDetails);
	}

	/**
	 * Get just the names of all countries
	 *
	 * @return array
	 */
	public function getAllCountryNames(): array
	{
		$results = [];

		foreach ($this->isoDetails as $country) {
			$results[] = $country->name;
		}

		asort($results);

		return $this->stripKey($results);
	}

	/**
	 * @param      $term
	 * @param bool $strict
	 *
	 * @return bool
	 */
	public function valid($term, bool $strict = true): bool
	{
		$previousStrict = $this->strict;
		$this->strict   = $strict;

		try {
			$result = (bool)$this->getCountry($term);
		}
		catch (Exception $e) {
			$result = false;
		}
		$this->strict = $previousStrict;

		return $result;
	}

	/**
	 * @param $expected
	 * @param $term
	 *
	 * @return bool
	 */
	public function validate($expected, $term): bool
	{
		if (!preg_match('/^[a-z0-9]{2,3}$/i', $expected)) {
			return false;
		}

		try {
			$key    = 'iso' . strlen($expected);
			$result = $this->getCountry($term);
		}
		catch (Exception $e) {
			return false;
		}

		return $expected == $result->$key();
	}

	/**
	 * @param      $expected
	 * @param      $term
	 * @param bool $throw Throw exception if not valid
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function assert($expected, $term, bool $throw = true): bool
	{
		if (!preg_match('/^[a-z0-9]{2,3}$/i', $expected)) {
			$exceptionMsg = sprintf('"%s" is not a valid country within ISO 3166-1', $term);

			throw new Exception($exceptionMsg);
		}

		$key    = 'iso' . strlen($expected);
		$result = $this->getCountry($term);

		if ($expected !== $result->{$key}()) {
			if (!$throw) {
				return false;
			}
			// Utilizes Respect/Validation for consistency is thrown errors
			$exceptionMsg = sprintf('"%s" does not match "%s"', $expected, $term);
			throw new Exception($exceptionMsg);
		}

		return true;
	}

	/**
	 * @param $term
	 *
	 * @return bool
	 * @throws Exception
	 */
	public function assertValid($term): bool
	{
		// Utilizes Respect/Validation for consistency is thrown errors
		if (!$this->getCountry($term)) {
			throw new Exception(sprintf('"%s" is not a valid country within ISO 3166-1', $term));
		}

		return true;
	}

	/**
	 * Is the country actually a US territory
	 *
	 * @param string|int $term
	 *
	 * @return bool
	 */
	public function isUSTerritory($term): bool
	{
		try {
			$result = $this->getCountry($term);
		}
		catch (Exception $e) {
			return false;
		}

		return null !== $result && $result->isUS;
	}

	/**
	 * Internal search for when exact matches were not found
	 *
	 * @param $term
	 *
	 * @return null
	 * @throws Exception
	 */
	private function search($term): ?Country
	{
		$term = strtolower($term);
		$term = preg_replace('/[^a-z ]+/', '', $term);

		/*
		 * Strip common starting words like "Nation of" or "Republic of"
		 */
		$term = preg_replace('/^(nation|republic|kingdom|commonwealth)\b/', ' ', $term);
		$term = preg_replace('/^((north|south|east|west)[a-z]*)\b/', ' ', $term);
		$term = preg_replace('/\b(of|the)\b/', ' ', $term);
		$term = preg_replace('/\s+/', ' ', $term);
		$term = trim($term);

		if (strlen($term) > 3) {
			$term = preg_replace('/\b(st|st\.)\b/', 'saint', $term);
		}
		$term = preg_replace('/^\b(nouvelle)\b/i', 'new', $term);

		$termsToReturnNullOn = [
			null,
			'saint',
			'united',
		];
		if (in_array($term, $termsToReturnNullOn)) {
			return null;
		}

		/*
		 * Do we already know of an alternative spelling?
		 */
		$altSpellings = include 'altSpellings.php';

		foreach ($altSpellings as $alt => $iso) {
			if ($term === $alt) {
				return $this->getCountryFromISO($iso);
			}
		}
		unset($altSpellings, $alt, $iso);

		/*
		 * Final attempt, Levenshtein math it
		 */
		if (strlen($term) === 2) {
			$key = 'iso2';
		} elseif (strlen($term) === 3) {
			$key = 'iso3';
		} else {
			$key = 'name';
		}
		$lowest = null;
		$result = null;

		foreach ($this->isoDetails as $country) {
			$haystack = strtolower($country->$key());
			$cost     = levenshtein($haystack, $term, 10, 9, 11);

			if ($cost < $lowest || $lowest === null) {
				$lowest = $cost;
				$result = $country;
			}
		}
		unset($haystack, $cost, $country);

		/*
		 * Decide if we have respectable results
		 */
		if (strlen($term) >= 4 && preg_match("/$term/i", strtolower($result->$key()))) {
			return $result;
		}
		if (preg_match("/{$result->$key()}/i", $term)) {
			return $result;
		}
		similar_text($term, $result->$key(), $percent);
		if ($percent >= 80) {
			return $result;
		}
		if ($percent >= 55 && strlen($term) / $lowest > 0.5) {
			return $result;
		}
		if (strlen($term) <= 3) {
			return null;
		}
		// does word count match
		$termWords   = count(explode(' ', $term));
		$resultWords = count(explode(' ', $result->$key()));
		if ($termWords >= 3 && $percent >= 55 && abs($termWords - $resultWords) <= 2) {
			return $result;
		}
		unset($result, $percent, $lowest);

		/*
		 * Does the term exist anywhere in the name?
		 */
		foreach ($this->isoDetails as $country) {
			if (stripos($country->$key(), $term) !== false) {
				return $country;
			}
		}

		$words = explode(' ', $term);
		if (count($words) === 1) {
			return null;
		}
		foreach ($words as $word) {
			if ($result = $this->search($word)) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * @param $key
	 *
	 * @return bool
	 */
	public function isValidSort($key): bool
	{
		if (in_array($key, $this->availSort)) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * @param $key  string Key to search
	 * @param $term string Term to search for
	 *
	 * @return ?Country
	 * @throws Exception
	 */
	private function findByKey(string $key, string $term): ?Country
	{
		foreach ($this->isoDetails as $details) {
			if (!property_exists($details, $key)) {
				throw new Exception(sprintf('Key "%s" not found in country details', $key));
			}
			if ($details->{$key}() == $term) {
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
	private function sort($array): array
	{
		$return = [];
		$key    = $this->sortOrder;

		foreach ($array as $item) {
			$return[$item->$key()] = $item;
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
	private function stripKey($array): array
	{
		$return = [];

		foreach ($array as $item) {
			$return[] = $item;
		}

		return $return;
	}
}
