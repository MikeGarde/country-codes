[![Packagist](https://img.shields.io/packagist/dt/mikegarde/country-codes.svg)](https://packagist.org/packages/mikegarde/country-codes)
[![Packagist](https://img.shields.io/packagist/dd/mikegarde/country-codes.svg)](https://packagist.org/packages/mikegarde/country-codes)
[![GitHub](https://img.shields.io/github/license/mikegarde/country-codes.svg)](https://github.com/MikeGarde/country-codes)
[![GitHub code size in bytes](https://img.shields.io/github/languages/code-size/mikegarde/country-codes.svg)](https://github.com/MikeGarde/country-codes)
[![Libraries.io dependency status for GitHub repo](https://img.shields.io/librariesio/github/mikegarde/country-codes.svg)](http://bit.ly/2Yuoi8w)
[![codecov](https://codecov.io/github/MikeGarde/country-codes/graph/badge.svg?token=EKO1IUVFV3)](https://codecov.io/github/MikeGarde/country-codes)

# Country Codes & US States

ISO 3166-1, 3166-2-US

## Install

Find on [Packagist](https://packagist.org/packages/mikegarde/country-codes),
and install using [Composer](http://getcomposer.org).

```shell
composer require mikegarde/country-codes
```

## Use

### Country Codes

```php
include 'vendor/autoload.php';

use Countries\Countries;

$countries = new Countries();
$result    = $countries->getCountry('US');
$result    = $countries->getCountry('USA');
$result    = $countries->getCountry('UnitedStates');
$result    = $countries->getCountry('United States');
$result    = $countries->getCountry('United States of America');

/*
$result = [
    'name'    => 'United States',
    'iso2'    => 'US',
    'iso3'    => 'USA',
    'isoNum'  => '840',
    'fips'    => 'US',
    'capital' => 'Washington',
    'isEU'    => 0,
    'isUK'    => 0,
    'isUS'    => 0,
];
*/
```

For your UI

```php
$countries = new Countries();
$results   = $countries->getAllCountries();

return json_encode($results);
```

US Territory

```php
$countries = new Countries(true);
if ($countries->isUSTerritory('PR'))
{
    echo 'Yep, a US Territory';
}
```

Do something for Canada

```php
if ($countries->validate('CA', $order['consignee']['countryCode']))
{
    echo 'Blame Canada';
}
```

### US States

Do something different when shipping outside the lower 48

```php
$stateTest = new US();

if ($stateTest->isCONUS($order['consignee']['state']))
{
    echo 'You can select USPS, UPS, or DHL';
}
else // OCONUS
{
   echo 'USPS is your only option for shipping to AK, HI, APO, or an FPO address';
}
```

## Local Development Notes

The numeric value after the command represents the PHP version

```shell
task build-8.3
task setup-8.3
task test-8.3
```

Alternatively you can run `task test-all` to build, setup, and test against all supported PHP versions.
