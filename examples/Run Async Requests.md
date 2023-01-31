# Run Async Requests to Outscraper API

The example shows how to send async requests to Outscraper API and retrieve the results later using request IDs (the requests are processed in parallel).

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
$results = array();
$running_request_ids = array();
$place_ids = array(
    'ChIJNw4_-cWXyFYRF_4GTtujVsw',
    'ChIJ39fGAcGXyFYRNdHIXy-W5BA',
    'ChIJVVVl-cWXyFYRQYBCEkX0W5Y',
    'ChIJScUP1R6XyFYR0sY1UwNzq-c',
    'ChIJmeiNBMeXyFYRzQrnMMDV8Jc',
    'ChIJifOTBMeXyFYRmu3EGp_QBuY',
    'ChIJ1fwt-cWXyFYR2cjoDAGs9UI',
    'ChIJ5zQrTzSXyFYRuiY31iE7M1s',
    'ChIJQSyf4huXyFYRpP9W4rtBelA',
    'ChIJRWK5W2-byFYRiaF9vVgzZA4'
);

foreach ($place_ids as &$place_id) {
    $response = $client->google_maps_search([$place_id], language: 'en', region: 'us', async_request: TRUE);
    array_push($running_request_ids, $response['id']);
}

$attempts = 5; # retry 5 times
while ($attempts && !empty($running_request_ids)) { # stop when no more attempts are left or when no more running request ids
    $attempts = $attempts - 1;
    sleep(60);

    foreach ($running_request_ids as &$request_id) {
        $result = $client->get_request_archive($request_id);

        if ($result['status'] != 'Success') {
            array_push($results, $result['data']);

            if (($key = array_search($del_val, $running_request_ids)) !== false) {
                unset($running_request_ids[$key]);
            }
        }
    }
}

print_r($results);
```
