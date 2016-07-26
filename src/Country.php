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

	public function getCountry($term)
	{
		if (v::stringType()->length(2, 3)->validate($term))
		{
			return $this->getCountryFromISO($term);
		}
		else
		{
			return $this->getCountryFromName($term);
		}
	}

	public function getCountryFromISO($iso)
	{
		v::stringType()->length(2, 3)->assert($iso);
	}

	public function getCountryFromName($term)
	{
		v::stringType()->notEmpty()->assert($term);

		foreach ($this->isoDetails as $details)
		{
			if ($details['name'] == $term)
			{
				return $details;
			}
		}
	}

	public function getNameFromISO($iso)
	{

	}

	public function getISOFromName($name)
	{
	}


}