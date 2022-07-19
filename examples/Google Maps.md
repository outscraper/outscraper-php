# Google Maps Scraper With PHP

The library provides real-time access to the places from Google Maps via [Outscraper API](https://app.outscraper.com/api-docs#tag/Google-Maps).

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
result = client.google_maps_search_v2(['restaurants brooklyn usa'], limit=20, language='en', region='us')

# Get data of the specific place by id
result = client.google_maps_search_v2(['ChIJrc9T9fpYwokRdvjYRHT8nI4'], language='en')

# Scrap Places by Two Queries
results = client.google_maps_search_v2(
    ['restaurants brooklyn usa', 'bars brooklyn usa'],
    limit=50, # limit of palces per each query
    language='en',
    region='US',
)
```
