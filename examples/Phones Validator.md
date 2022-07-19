# Phone Numbers Enricher/Validator With PHP

Returns phones carrier data (name/type), validates phones, ensures messages deliverability via [Outscraper API](https://app.outscraper.com/api-docs#tag/Phones/paths/~1phones-enricher/get).

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
# Search contacts from website:
result = client.phones_enricher(['12812368208'])
```
