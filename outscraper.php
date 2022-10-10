<?php


/**
 * OutscraperClient - PHP SDK that allows using Outscraper's services and Outscraper's API.
 *
 * @copyright  Outscraper 2021
 * @license    https://raw.githubusercontent.com/outscraper/outscraper-php/main/LICENSE
 * @version    Release: 1.1.0
 * @link       https://github.com/outscraper/outscraper-php
 */
class OutscraperClient {
    public $version = "2.0.0";
    private $api_url = "https://api.app.outscraper.com";
    private $api_headers;
    private $max_ttl = 60 * 60;
    private $requests_pause = 5;

    /**
     * @param string $api_key API KEY from https://app.outscraper.com/profile
     */
    public function __construct(string $api_key = NULL, int $requests_pause = 5) {
        if($api_key == NULL)
            throw new Exception("api_key must have a value");

        $headers = array();
        $headers[] = "Accept: application/json";
        $headers[] = "Client: PHP SDK {$this->version}";
        $headers[] = "X-API-KEY: {$api_key}";

        $this->api_headers = $headers;
        $this->requests_pause = $requests_pause;
    }

    private function wait_request_archive(string $request_id) : array {
        $ttl = $this->max_ttl / $this->requests_pause;

        while ($ttl > 0) {
            $ttl--;
            sleep($this->requests_pause);

            $result = $this->get_request_archive($request_id);
            if ($result["status"] != "Pending") {
                return $result;
            }
        }

        throw new Exception("Timeout exceeded");
    }

    private function make_get_request(string $url) : array {
        $url = preg_replace('/%5B[0-9]+%5D/simU', '', $url);

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, "{$this->api_url}/{$url}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_HTTPHEADER, $this->api_headers);

        $result = json_decode(curl_exec($ch), true);
        if (curl_errno($ch)) {
            throw new Exception("API Error: " . curl_error($ch));
        }
        curl_close($ch);

        if (array_key_exists("error", $result) && $result["error"] == TRUE) {
            throw new Exception($result["errorMessage"]);
        }

        return $result;
    }

    private function to_array(string|array $value) : array {
        if (is_array($value)) {
            return $value;
        } else {
            return [$value];
        }
    }

    /**
     * Fetch up to 100 of your last requests.
     *
     * @return array requests history
     */
    public function get_requests_history() : array {
        return $this->make_get_request("requests");
    }

    /**
     * Fetch request data from archive
     *
     * @param string $request_id id for the request/task provided by ["id"]
     *
     * @return array result from the archive
     */
    public function get_request_archive(string $request_id) : array {
        if($request_id == NULL)
            throw new Exception("request_id must have a value");
        return $this->make_get_request("requests/{$request_id}");
    }

    /**
     * Returns search results from Google based on a given search query (or many queries).
     *
     * @param string|array $query Parameter defines the queries to search on Google (e.g., bitcoin, 37th president of usa). Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $pages_per_query Parameter specifies the limit of pages to return from one query.
     * @param string $uule Google UULE parameter is used to encode a place or an exact location (with latitude and longitude) into a code. By using it you can see a Google result page like someone located at the specified location.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     *
     * @return array request/task result
     */
    public function google_search(
        string|array $query, int $pages_per_query = 1, string $uule = "", string $language = "en", string $region = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "pagesPerQuery" => $pages_per_query,
            "uule" => $uule,
            "language" => $language,
            "region" => $region
        ));
        $result = $this->make_get_request("google-search-v3?{$params}");
        return $result["data"];
    }

    /**
     * Get data from Google Maps
     *
     * @param string|array $query Parameter defines the query or queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param bool $extract_contacts Parameter specifies whether the bot will scrape additional data (emails, social links, site keywords…) from companies’ websites. It increases the time of the extraction.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param bool $drop_duplicates Parameter specifies whether the bot will drop the same organizations from different queries. Using the parameter combines results from each query inside one big array.
     *
     * @return array request/task result
     */
    public function google_maps_search_v1(
        string|array $query, string $language = "en", string $region = NULL, int $limit = 400,
        bool $extract_contacts = FALSE, string $coordinates = NULL, bool $drop_duplicates = FALSE
    ) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "coordinates" => $coordinates,
            "extractContacts" => $extract_contacts,
            "dropDuplicates" => $drop_duplicates,
        ));
        $result = $this->make_get_request("maps/search?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Get data from Google Maps (speed optimized endpoint)
     *
     * @param string|array $query Parameter defines the query or queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param bool $extract_contacts Parameter specifies whether the bot will scrape additional data (emails, social links, site keywords…) from companies’ websites. It increases the time of the extraction.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param bool $drop_duplicates Parameter specifies whether the bot will drop the same organizations from different queries. Using the parameter combines results from each query inside one big array.
     * @param int $skip Skip first N places, where N should be multiple to 20 (e.g. 0, 20, 40). It's commonly used in pagination.
     *
     * @return array request/task result
     */
    public function google_maps_search(
        string|array $query, string $language = "en", string $region = NULL, int $limit = 400,
        string $coordinates = NULL, bool $drop_duplicates = FALSE, int $skip = 0
    ) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "coordinates" => $coordinates,
            "dropDuplicates" => $drop_duplicates,
            "skipPlaces" => $skip,
            "async" => FALSE,
        ));
        $result = $this->make_get_request("maps/search-v2?{$params}");
        return $result["data"];
    }

    /**
     * Get reviews from Google Maps
     *
     * @param string|array $query Parameter defines the query or queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param int $reviews_limit Parameter specifies the limit of reviews to extract from one organization.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param int $cutoff Parameter specifies the maximum timestamp value for reviews. Using the cutoff parameter overwrites sort parameter to newest.
     * @param int $cutoffRating Parameter specifies the maximum (for lowest_rating sorting) or minimum (for highest_rating sorting) rating for reviews. Using the cutoffRating requires sorting to be set to "lowest_rating" or "highest_rating".
     * @param string $sort Parameter specifies one of the sorting types. Available values: "most_relevant", "newest", "highest_rating", "lowest_rating".
     * @param string $reviews_query Parameter specifies the query to search among the reviews (e.g. wow, amazing, horrible place).
     *
     * @return array request/task result
     */
    public function google_maps_reviews_v2(
        string|array $query, string $language = "en", string $region = NULL, int $limit = 1,
        int $reviews_limit = 100, string $coordinates = NULL, int $cutoff = NULL, int $cutoff_rating = NULL,
        string $sort = "most_relevant", string $reviews_query = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "reviewsPerOrganizationLimit" => $reviews_limit,
            "coordinates" => $coordinates,
            "cutoff" => $cutoff,
            "cutoffRating" => $cutoff_rating,
            "reviewsQuery" => $reviews_query,
            "sort" => $sort
        ));
        $result = $this->make_get_request("maps/reviews-v2?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Get reviews from Google Maps (speed optimized)
     *
     * @param string|array $query Parameter defines the query or queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param int $reviews_limit Parameter specifies the limit of reviews to extract from one organization.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param int $cutoff Parameter specifies the maximum timestamp value for reviews. Using the cutoff parameter overwrites sort parameter to newest.
     * @param int $cutoffRating Parameter specifies the maximum (for lowest_rating sorting) or minimum (for highest_rating sorting) rating for reviews. Using the cutoffRating requires sorting to be set to "lowest_rating" or "highest_rating".
     * @param string $sort Parameter specifies one of the sorting types. Available values: "most_relevant", "newest", "highest_rating", "lowest_rating".
     * @param string $reviews_query Parameter specifies the query to search among the reviews (e.g. wow, amazing, horrible place).
     *
     * @return array request/task result
     */
    public function google_maps_reviews(
        string|array $query, string $language = "en", string $region = NULL, int $limit = 1,
        int $reviews_limit = 100, string $coordinates = NULL, int $cutoff = NULL, int $cutoff_rating = NULL,
        string $sort = "most_relevant", string $reviews_query = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "reviewsPerOrganizationLimit" => $reviews_limit,
            "coordinates" => $coordinates,
            "cutoff" => $cutoff,
            "cutoffRating" => $cutoff_rating,
            "reviewsQuery" => $reviews_query,
            "sort" => $sort,
            "async" => FALSE,
        ));
        $result = $this->make_get_request("maps/reviews-v3?{$params}");
        return $result["data"];
    }

    /**
     * Return email addresses, social links and phones from domains in seconds.
     *
     * @param string|array $query Domains or links (e.g., outscraper.com).
     *
     * @return array json result
     */
    public function emails_and_contacts(string|array $query) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "async" => FALSE,
        ));
        $result = $this->make_get_request("emails-and-contacts?{$params}");
        return $result["data"];
    }

    /**
     * Returns phones carrier data (name/type), validates phones, ensures messages deliverability.
     *
     * @param string|array $query Phone number (e.g., +1 281 236 8208).
     *
     * @return array json result
     */
    public function phones_enricher(string|array $query) : array {
        $params = http_build_query(array(
            "query" => $this->to_array($query),
            "async" => FALSE,
        ));
        $result = $this->make_get_request("phones-enricher?{$params}");
        return $result["data"];
    }
}

?>
