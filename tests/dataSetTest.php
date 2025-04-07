<?php

use PHPUnit\Framework\TestCase;
use Countries\Countries;

class dataSetTest extends TestCase
{

	public function testConstruction()
	{
		$countries = new Countries();
		$dataSet   = include 'dataSet.php';

		foreach ($dataSet as $term => $desired) {
			$result = $countries->getCountry($term);

			if (null === $desired) {
				$msg = sprintf('Expected null for "%s" but got "%s"', $term, $result ? $result->name : 'null');
				$this->assertNull($result, $msg);
			} elseif (null === $result) {
				$this->fail('Result was null for ' . $term);
			} else {
				$msg = '"%s" was expecting iso "%s" but got "%s" for "%s"';
				$msg = sprintf($msg, $term, $desired, $result->iso2, $result->name);

				$this->assertEquals($desired, $result->iso2, $msg);
			}
		}
	}
}
