<?php

use PHPUnit\Framework\TestCase;
use Countries\Countries;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\AllOfException;
use Respect\Validation\Exceptions\ComponentException;

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

		$this->assertEquals('US', $results['iso2']);
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

	public function testValidation()
	{
		$countries = new Countries();

		$this->assertTrue($countries->validate('US', 'United States'));
		$this->assertTrue($countries->validate('USA', 'United States'));
		$this->assertFalse($countries->validate('US', 'Canada'));
	}

	public function testIsValid()
	{
		$countries = new Countries();

		$this->assertTrue($countries->valid('United States'));
		$this->assertFalse($countries->valid('United Front of Alien Planets'));
	}

	public function testAssertion()
	{
		$countries = new Countries();

		$this->assertTrue($countries->assert('USA', 'United States'));
	}

	public function testAssertionFail()
	{
		$this->expectException(ComponentException::class);

		$countries = new Countries();
		$countries->assert('ZZ', 'United States');
	}

	public function testAssertValid()
	{
		$countries = new Countries();

		$this->assertTrue($countries->assertValid('United States'));
	}

	public function testAssertValidFail()
	{
		$this->expectException(ComponentException::class);

		$countries = new Countries();
		$countries->assertValid('Not A Country (yet)');
	}

	public function testUSTerritories()
	{
		$countries = new Countries();

		$this->assertTrue($countries->isUSTerritory('Guam'));
		$this->assertTrue($countries->isUSTerritory('Northern Mariana Islands'));
		$this->assertTrue($countries->isUSTerritory('Puerto Rico'));
		$this->assertTrue($countries->isUSTerritory('US Virgin Islands'));
		$this->assertTrue($countries->isUSTerritory('Micronesia'));
		$this->assertTrue($countries->isUSTerritory('Northern Mariana Islands'));
		$this->assertTrue($countries->isUSTerritory('Palau'));
		$this->assertTrue($countries->isUSTerritory('American Samoa'));
		$this->assertTrue($countries->isUSTerritory('PR'));
		$this->assertFalse($countries->isUSTerritory('Canada'));
	}
}