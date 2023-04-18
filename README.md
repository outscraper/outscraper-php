# Outscraper PHP Library

The library provides convenient access to the [Outscraper API](https://app.outscraper.com/api-docs) from applications written in the PHP language. Allows using [Outscraper's services](https://outscraper.com/services/) from your code.

[API Docs](https://app.outscraper.com/api-docs)

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

## Google Maps Scraper

Scrape Google Mpas results bu query "asian restaurants Berlin, Germany".

```php
$results = $client->google_maps_search(['asian restaurants Berlin, Germany'], 'en', 'DE');

print_r($results);
```

## Google Maps Reviews Scraper

Scrape Google Mpas reviews from Statue of Liberty National Monument.
```php
$results = $client->google_maps_reviews([
    'https://www.google.com/maps/place/Statue+of+Liberty+National+Monument/@40.6892494,-74.0466891,17z/data=!3m1!4b1!4m5!3m4!1s0x89c25090129c363d:0x40c6a5770d25022b!8m2!3d40.6892494!4d-74.0445004'
], limit: 10, sort: 'newest');
// you can use direct links, IDs, or names as input for query

print_r($results);
```

Scrape Emails & Contacts from domains.
```php
$results = $client->emails_and_contacts([
    'outscraper.com'
]);

print_r($results);
```

[More examples](https://github.com/outscraper/outscraper-php/tree/master/examples)

## Contributing
Bug reports and pull requests are welcome on GitHub at https://github.com/outscraper/outscraper-php.
