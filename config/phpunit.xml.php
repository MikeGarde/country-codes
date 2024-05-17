<?php
// Load the XML
$xml = simplexml_load_file('./config/phpunit.xml');

// Get the PHP version
$phpVersion = phpversion();

// Adjust the XML based on the PHP version
if (version_compare($phpVersion, '8.3.0', '<')) {
    unset($xml->coverage);
}

// Versions 7.4 - 8.0
if (version_compare($phpVersion, '8.1.0', '<')) {
    unset($xml['cacheDirectory']);
    unset($xml->source);
}

// Save the modified XML back to the file
$xml->asXML('./phpunit.xml');
