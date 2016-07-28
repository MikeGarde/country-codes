<?php

use PHPUnit\Framework\TestCase;
use Countries\Countries;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\AllOfException;

class countryTest extends TestCase {

	public function testConstruction()
	{
		new Countries();
	}

	public function testPrefConstruction()
	{
		new Countries(['name', 'iso2']);
	}

	public function testExceptionConstruction()
	{
		$this->expectException(AllOfException::class);
		new Countries('fail - string');
	}

	public function testExceptionConstruction2()
	{
		$this->expectException(AllOfException::class);
		new Countries(['fail', 'array']);
	}

	public function testAltSpellings()
	{
		$altSpellings = include 'src/altSpellings.php';

		foreach ($altSpellings as $alt => $iso)
		{
			$msg = '"%s" alt spelling "%s" has capitalization issues';
			$msg = sprintf($msg, $iso, $alt);

			$this->assertTrue(v::stringType()->lowercase()->validate($alt), $msg);
			$this->assertTrue(v::stringType()->uppercase()->validate($iso), $msg);

			$msg = '"%s" alt spelling "%s" has punctuation issues';
			$msg = sprintf($msg, $iso, $alt);

			$this->assertTrue(preg_match('/^[a-z ]+$/', $alt) === 1, $msg);
		}
	}

	public function testGetCountry1()
	{
		$countries = new Countries();
		$results   = $countries->getCountry('United States');

		$this->assertEquals($results['iso2'], 'US');
	}

	public function testGetCountry2()
	{
		$countries = new Countries();

		$results = $countries->getCountry('United States of America');
		$this->assertEquals('US', $results['iso2']);

		$results = $countries->getCountry('UnitedStates');
		$this->assertEquals('US', $results['iso2']);

		$results = $countries->getCountry('Canida');
		$this->assertEquals('CA', $results['iso2']);

		$results = $countries->getCountry('Kyrgyztan');
		$this->assertEquals('KG', $results['iso2']);

		$results = $countries->getCountry('St Maarten');
		$this->assertEquals('SX', $results['iso2']);
	}

	public function testGetCountry3()
	{
		$countries = new Countries();

		$results = $countries->getCountry('Vatican');
		$this->assertEquals('VA', $results['iso2']);

		$results = $countries->getCountry('Lao People\'s Democratic Republic');
		$this->assertEquals('LA', $results['iso2']);

	}

	public function testGetAllCountries()
	{
		$countries = new Countries();
		$results   = $countries->getAllCountries();

		$this->assertCount(250, $results);
	}

	public function testGetAllCountryNames()
	{
		$countries = new Countries();
		$results   = $countries->getAllCountryNames();

		$this->assertCount(250, $results);
		$this->assertContains('United States', $results);
	}

	public function testSortOrder()
	{
		$countries = new Countries();
		$countries->setSort('capital');
		$results = $countries->getAllCountries();

		$this->assertEquals('AE', $results[1]['iso2']);
	}

	public function testBadSortOrder()
	{
		$this->expectException(AllOfException::class);
		$countries = new Countries();
		$countries->setSort('bad');
	}

	public function testPreferences()
	{
		$preferences = ['name', 'iso2'];
		$countries   = new Countries($preferences);

		$results = $countries->getCountry('United States of America');

		$this->assertEquals('US', $results['iso2']);
		$this->assertEquals('United States', $results['name']);
		$this->assertEquals(2, count($results));


		$countries->setPref(['name', 'iso3']);
		$results = $countries->getCountry('UnitedStates');

		$this->assertEquals('USA', $results['iso3']);
		$this->assertEquals('United States', $results['name']);
		$this->assertEquals(2, count($results));
	}

}