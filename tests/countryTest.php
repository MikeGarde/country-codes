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

	public function testUnitedStatesLookup1() {
		$country = new Country();

		$results = $country->getCountry('United States');
	}
}