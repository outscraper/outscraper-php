# Google Directions  With PHP

Returns directions between two points from Google Maps. [Outscraper API](https://app.outscraper.cloud/api-docs#tag/Google/paths/~1maps~1directions/get).

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
# Returns directions:
$origins = ['29.696596,76.994928', '29.696596,76.994928'];
$destinations = ['30.715966244353,76.8053887016268', '30.723065,76.770169'];

$results = $client->google_maps_directions(
    $origins,
    $destinations,
    async_request: false
);

```
