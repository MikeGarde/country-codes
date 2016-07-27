<?php namespace Country;

use PHPUnit\Framework\TestCase;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\AllOfException;

class dataSetTest extends TestCase {

	public function testConstruction()
	{
		$country = new Country();
		$dataSet = include 'dataSet.php';

		foreach ($dataSet as $term => $desired)
		{
			$result = $country->getCountry($term);
			$msg    = '"%s" was expecting iso "%s" but got "%s" for "%s"';
			$msg    = sprintf($msg, $term, $desired, $result['iso2'], $result['name']);

			$this->assertEquals($desired, $result['iso2'], $msg);
		}
	}
}