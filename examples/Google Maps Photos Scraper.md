# Google Maps Photos Scraper With PHP

Returns Google Maps photos from places when using search queries (e.g., restaurants, Manhattan, NY, USA) or from a single place when using IDs or names (e.g., NoMad Restaurant, NY, USA, 0x886916e8bc273979:0x5141fcb11460b226, ChIJu7bMNFV-54gR-lrHScvPRX4).
In case no photos were found by your search criteria, your search request will consume the usage of one photo.[Outscraper API](https://app.outscraper.cloud/api-docs#tag/Google/paths/~1maps~1photos-v3/get).

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
# Get information about the places photos:
$results = $client->get_google_maps_photos(['The NoMad Restaurant, NY, USA']);
```
