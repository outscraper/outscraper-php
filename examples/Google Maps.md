# Google Maps Scraper With PHP

The library provides real-time access to the places from Google Maps via [Outscraper API](https://app.outscraper.com/api-docs#tag/Google-Maps).
It allows easy scraping of [businesses information](https://outscraper.com/google-maps-scraper/#dictionary) from Google Maps.

![screencast](https://media.giphy.com/media/v1.Y2lkPTc5MGI3NjExMzU0YjRjY2UyMDMxZDllNjNiMTE0MTg3MWIxYmEzODhmYjYyZjNjYiZjdD1n/Ah78imt8G2mSGAfqPm/giphy.gif)

## Installation

### Composer

You can install the bindings via [Composer](http://getcomposer.org/). Run the following command:

```bash
composer require outscraper/outscraper
```

To use the bindings, use Composer's [autoload](https://getcomposer.org/doc/01-basic-usage.md#autoloading):

```php
require_once('vendor/autoload.php');
```

### Manual Installation

If you do not wish to use Composer, you can download the [latest release](https://github.com/outscraper/outscraper-php/releases). Then, to use the bindings, include the `init.php` file.

```php
require_once('/path/to/outscraper-php/init.php');
```
[Link to the PHP package page](https://packagist.org/packages/outscraper/outscraper)

## Initialization
```php
$client = new OutscraperClient("SECRET_API_KEY");
```
[Link to the profile page to create the API key](https://app.outscraper.com/profile)

## Usage

```php
# Search for businesses in specific locations:
$results = $client->google_maps_search(['restaurants brooklyn usa'], limit: 20, language: 'en', region: 'us');

# Get data of the specific place by id
$results = $client.google_maps_search(['ChIJrc9T9fpYwokRdvjYRHT8nI4'], language: 'en');

# Scrap Places by Two Queries
$results = $client->google_maps_search(
    ['restaurants brooklyn usa', 'bars brooklyn usa'],
    limit: 50, # limit of palces per each query
    language: 'en',
    region: 'US',
);

foreach ($results as &$query_places) {
    foreach ($query_places as &$place) {
        print($place['query']);
        print($place['name']);
        print($place['phone']);
        print($place['site']);
    }
};

# Scrap Places by Place Ids
$results = $client->google_maps_search(
    ["ChIJ8ccnM7dbwokRy-pTMsdgvS4", "ChIJN5X_gWdZwokRck9rk2guJ1M", "ChIJxWLy8DlawokR1jvfXUPSTUE"],
    limit: 1, # limit of palces per each query
);

foreach ($results as &$query_places) {
    foreach ($query_places as &$place) {
        print($place['query']);
        print($place['place_id']);
    }
};
```
