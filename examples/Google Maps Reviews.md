# Google Maps Reviews Scraper With PHP

The library provides real-time access to the reviews from Google Maps via [Outscraper API](https://app.outscraper.com/api-docs#tag/Google-Reviews).

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
# Get reviews of the specific place by id
result = client.google_maps_reviews_v3(['ChIJrc9T9fpYwokRdvjYRHT8nI4'], reviews_limit=20, language='en')

# Get reviews for places found by search query
result = client.google_maps_reviews_v3(['Memphis Seoul brooklyn usa'], reviews_limit=20, limit=500, language='en')

# Get only new reviews during last 24 hours
yesterday_timestamp = 1657980986
result = client.google_maps_reviews_v3(
    ['ChIJrc9T9fpYwokRdvjYRHT8nI4'], sort='newest', cutoff=yesterday_timestamp, reviews_limit=100, language='en')

# Scrap Places Reviews by Place Ids
results = client.google_maps_reviews_v3(
    ["ChIJN5X_gWdZwokRck9rk2guJ1M", "ChIJxWLy8DlawokR1jvfXUPSTUE"],
    reviews_limit=20, # limit of reviews per each place
    limit=1, # limit of palces per each query
)
```
