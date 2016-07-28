<?php

use PHPUnit\Framework\TestCase;
use Countries\Countries;

class dataSetTest extends TestCase {

	public function testConstruction()
	{
		$countries = new Countries();
		$dataSet = include 'dataSet.php';

		foreach ($dataSet as $term => $desired)
		{
			$result = $countries->getCountry($term);
			$msg    = '"%s" was expecting iso "%s" but got "%s" for "%s"';
			$msg    = sprintf($msg, $term, $desired, $result['iso2'], $result['name']);

			$this->assertEquals($desired, $result['iso2'], $msg);
		}
	}
}