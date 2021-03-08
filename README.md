# Google Maps scraper in PHP

PHP SDK that allows scraping Google Maps and Google Reviews via [OutScraper API](https://outscraper.com).

[The API documentation](https://app.outscraper.com/api-docs).

## Installation

The Google Maps scraper PHP SDK can be installed with [Composer](https://getcomposer.org/). Run this command:

```sh
composer require outscraper/google-maps-scraper-php 
```

## Usage

Scrape Google Mpas results bu query "asian restaurants Berlin, Germany".

```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

$client = new ApiClient("your API_KEY from https://app.outscraper.com/profile");
$result = $client->google_maps_search(['asian restaurants Berlin, Germany'], 'en', 'DE');

print_r($result);
```

Scrape Google Mpas reviews from Statue of Liberty National Monument.
```php
require_once __DIR__ . '/vendor/autoload.php'; // change path as needed

$result = $client->google_maps_business_reviews([
    'https://www.google.com/maps/place/Statue+of+Liberty+National+Monument/@40.6892494,-74.0466891,17z/data=!3m1!4b1!4m5!3m4!1s0x89c25090129c363d:0x40c6a5770d25022b!8m2!3d40.6892494!4d-74.0445004'
], limit: 10, sort: 'newest');
// you can use direct links, IDs, or names as input for query

print_r($result);
```
