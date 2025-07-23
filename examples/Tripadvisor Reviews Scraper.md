# Tripadvisor Reviews Scraper With PHP

Returns reviews from Tripadvisor businesses.
In case no reviews were found by your search criteria, your search request will consume the usage of one review. [Outscraper API](https://app.outscraper.cloud/api-docs#tag/Reviews-and-Comments/paths/~1tripadvisor-reviews/get).

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
# Get information about business:
$results = $client->tripadvisor_reviews(['https://www.tripadvisor.com Restaurant_Review-g187147-d12947099-Reviews-Mayfair_Garden-Paris_Ile_de_France.html']);
```
