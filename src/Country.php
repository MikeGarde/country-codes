<?php

namespace Countries;

/**
 * Class Country
 *
 * @property-read $name
 * @property-read $iso2
 * @property-read $iso3
 * @property-read $isoNum
 * @property-read $fips
 * @property-read $capital
 * @property-read $isEU
 * @property-read $isUK
 * @property-read $isUS
 *
 * @package Countries
 */
class Country
{
	private string  $name;
	private array   $altNames = [];
	private string  $iso2;
	private string  $iso3;
	private ?string $isoNum;
	private ?string $fips;
	private string  $capital;
	private bool    $isEU;
	private bool    $isUK;
	private bool    $isUS;
	private string  $toStringKey;

	public function __construct(array $data, string $toStringKey = 'iso2')
	{
		$this->toStringKey = $toStringKey;

		$this->name    = $data['commonName'] ?? $data['fullName'];
		$this->iso2    = $data['iso2'];
		$this->iso3    = $data['iso3'];
		$this->isoNum  = $data['isoNum'];
		$this->fips    = $data['fips'];
		$this->capital = $data['capital'];
		$this->isEU    = $data['isEU'];
		$this->isUK    = $data['isUK'];
		$this->isUS    = $data['isUS'];

		$this->addAltName($data['commonName']);
		$this->addAltName($data['fullName']);
		$this->addAltName($data['pastNames']);
		$this->addAltName($data['altNames']);
		$this->addAltName($data['name']);
		$this->addAltName($data['shortName']);
	}

	/**
	 * Add an alternative name to the list of names
	 *
	 * @param string|null $name
	 * @return void
	 */
	private function addAltName(?string $name): void
	{
		if (empty($name) || in_array($name, $this->altNames)) {
			return;
		}
		$this->altNames[] = $name;
	}

	/**
	 * Get the value of a property
	 *
	 * @param string $name
	 *
	 * @return mixed
	 */
	public function __get($name)
	{
		if (property_exists($this, $name)) {
			return $this->$name;
		}
		return null;
	}

	/**
	 * Magic method to convert to an array
	 *
	 * @return array
	 */
	public function toArray(): array
	{
		$return = [];
		foreach ($this as $key => $value) {
			$return[$key] = $value;
		}
		return $return;
	}

	/**
	 * Magic method to convert to a string
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return $this->{$this->toStringKey};
	}

	public function name(): string
	{
		return $this->name;
	}

	public function iso2(): string
	{
		return $this->iso2;
	}

	public function iso3(): string
	{
		return $this->iso3;
	}

	public function isoNum(): string
	{
		return $this->isoNum;
	}

	public function fips(): string
	{
		return $this->fips;
	}

	public function capital(): string
	{
		return $this->capital;
	}

	public function isEU(): bool
	{
		return $this->isEU;
	}

	public function isUK(): bool
	{
		return $this->isUK;
	}

	public function isUS(): bool
	{
		return $this->isUS;
	}

	/**
	 * Is the country currently recognised
	 *
	 * @return bool
	 */
	public function isCurrent(): bool
	{
		return ($this->isoNum);
	}
}
