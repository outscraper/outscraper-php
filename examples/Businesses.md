# Businesses Search With PHP

Get business listings (POI data) with optional enrichment via [Outscraper API](https://app.outscraper.com/api-docs#tag/Businesses).

You can describe your request in two ways:

1. **Structured JSON** (`filters`, `fields`, `limit`, `cursor`, `include_total`)
2. **Plain-text AI query** (`query`)

When both are provided, they are combined with the following priority:

| Parameter type | Behavior |
|---|---|
| `filters`, `fields` | Merged from JSON + AI query |
| `limit`, `cursor`, `include_total` | AI query result has priority |
| Missing values | SDK defaults are used |

---

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

---

## Initialization

```php
$client = new OutscraperClient("SECRET_API_KEY");
```

[Link to the profile page to create the API key](https://app.outscraper.com/profile)

---

## Usage

### 1) Search with structured filters (single page)

```php
$filters = [
    'country_code' => 'US',
    'states' => ['CA'],
    'types' => ['restaurant', 'cafe'],
    'has_website' => true,
    'business_statuses' => ['operational'],
];

$fields = ['name', 'address', 'phone', 'website', 'rating', 'reviews'];

$page = $client->businessesSearch(
    $filters,
    25,     // limit
    false,  // include_total
    null,   // cursor
    $fields,
    false   // async_request
);

$items = $page['items'] ?? [];
$nextCursor = $page['next_cursor'] ?? null;
$hasMore = (bool)($page['has_more'] ?? false);
```

### 2) Search using a plain-text AI query

```php
$query = 'Find cafes and hotels in California and Illinois. '
    . 'Only rating 4.2+. Return fields name, address, territory, rating, reviews. '
    . 'Set limit to 15 and do not include total.';

$page = $client->businessesSearch(
    [],     // filters
    10,     // fallback limit (AI may override)
    false,
    null,
    null,   // fields
    false,
    false,
    null,
    $query
);

$items = $page['items'] ?? [];
```

Tip: on PHP 8+, you can use named arguments:

```php
$page = $client->businessesSearch(
    query: $query,
    async_request: false
);
```

### 3) Mixed mode (JSON + AI query)

```php
$filters = [
    'country_code' => 'US',
    'states' => ['CA'],
];

$query = 'Add types cafe and hotel. Business status is closed_temporarily. '
    . 'Return fields name, address, rating, reviews. Limit 15.';

$page = $client->businessesSearch(
    $filters,
    10,
    false,
    null,
    ['name'],
    false,
    false,
    null,
    $query
);
```

### 4) Iterate through all results (auto-pagination)

```php
$items = $client->businessesIterSearch(
    ['country_code' => 'US', 'states' => ['CA'], 'business_statuses' => ['operational']],
    100,
    ['name', 'phone', 'address', 'rating']
);

foreach ($items as $business) {
    print_r($business);
}
```

---

See also: [Businesses / POI API docs](https://app.outscraper.com/api-docs#tag/Businesses).
