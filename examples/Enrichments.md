# Using Enrichments With PHP

Using enrichments with [Outscraper API](https://app.outscraper.cloud/api-docs).

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
$client = new OutscraperClient('SECRET_API_KEY');
```
[Link to the profile page to create the API key](https://app.outscraper.cloud/profile)

## Usage

```php
// Enriching Google Maps results with Contacts & Leads, Email Validator,
// Company Insights, Phone Enricher, and Whitepages Phones:
$results = $client->google_maps_search(
    query: "bars ny usa",
    enrichment: [
        'contacts_n_leads',           // Contacts & Leads Enrichment
        'emails_validator_service',   // Email Address Verifier
        'company_insights_service',   // Company Insights
        'phones_enricher_service',    // Phone Numbers Enricher
        'whitepages_phones',          // Phone Identity Finder
    ]
);
```

## Available values

`contacts_n_leads` — **Contacts & Leads Enrichment**: finds emails, social links, phones, and other contacts from websites;

`emails_validator_service` — **Email Address Verifier**: validates emails, checks deliverability, filters out blacklists, spam traps, and complainers, while significantly reducing your bounce rate;

`disposable_email_checker` — **Disposable Emails Checker**: checks origins of email addresses (disposable, free, or corporate);

`company_insights_service` — **Company Insights**: finds company details such as revenue, size, founding year, public status, etc;

`phones_enricher_service` — **Phone Numbers Enricher**: returns phones carrier data (name/type), validates phones, ensures messages deliverability;

`trustpilot_service` — **Trustpilot Scraper**: returns data from a list of businesses;

`whitepages_phones` - **Phone Identity Finder**: returns insights about phone number owners (name, address, etc.);

`ai_chain_info` - **Chain Info**: identifies if a business is part of a chain, adding a true/false indication to your data for smarter targeting.
