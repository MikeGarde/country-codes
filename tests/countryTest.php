<?php namespace Country;

use PHPUnit\Framework\TestCase;
use Respect\Validation\Exceptions\AllOfException;

class countryTest extends TestCase {

	public function testConstruction()
	{
		new Country();
	}

	public function testPrefConstruction()
	{
		new Country(['name', 'iso2']);
	}

	public function testExceptionConstruction()
	{
		$this->expectException(AllOfException::class);
		new Country('fail - string');
	}

	public function testExceptionConstruction2()
	{
		$this->expectException(AllOfException::class);
		new Country(['fail', 'array']);
	}

	public function testGetCountry1()
	{
		$country = new Country();
		$results = $country->getCountry('United States');

		$this->assertEquals($results['iso2'], 'US');
	}

	public function testGetCountry2()
	{
		$country = new Country();

		$results = $country->getCountry('United States of America');
		$this->assertEquals('US', $results['iso2']);

		$results = $country->getCountry('UnitedStates');
		$this->assertEquals('US', $results['iso2']);

		$results = $country->getCountry('Canida');
		$this->assertEquals('CA', $results['iso2']);

		$results = $country->getCountry('Kyrgyztan');
		$this->assertEquals('KG', $results['iso2']);

		$results = $country->getCountry('St Maarten');
		$this->assertEquals('SX', $results['iso2']);
	}

	public function testGetCountry3() {

		$country = new Country();

		$results = $country->getCountry('Vatican');
		$this->assertEquals('VA', $results['iso2']);

		$results = $country->getCountry('Lao People\'s Democratic Republic');
		$this->assertEquals('LA', $results['iso2']);

	}

	public function testGetAllCountries()
	{
		$country = new Country();
		$results = $country->getAllCountries();

		$this->assertCount(250, $results);
	}

	public function testGetAllCountryNames()
	{
		$country = new Country();
		$results = $country->getAllCountryNames();

		$this->assertCount(250, $results);
		$this->assertContains('United States', $results);
	}

	public function testSortOrder()
	{
		$country = new Country();
		$country->setSort('capital');
		$results = $country->getAllCountries();

		$this->assertEquals('AE', $results[1]['iso2']);
	}

	public function testBadSortOrder()
	{
		$this->expectException(AllOfException::class);
		$country = new Country();
		$country->setSort('bad');
	}

}