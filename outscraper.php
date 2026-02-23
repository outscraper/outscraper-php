<?php


/**
 * OutscraperClient - PHP SDK that allows using Outscraper's services and Outscraper's API.
 *
 * @copyright  Outscraper 2025
 * @license    https://raw.githubusercontent.com/outscraper/outscraper-php/main/LICENSE
 * @version    Release: 4.2.5
 * @link       https://github.com/outscraper/outscraper-php
 */

 const QUERY_DELIMITER = '    ';

function format_direction_queries(string|array $q): array {
    if (is_array($q) && !empty($q) && is_array($q[0])) {
        return array_map(
            fn($pair) => implode(QUERY_DELIMITER, $pair),
            $q
        );
    }

    if (is_array($q)) {
        return $q;
    }

    return [$q];
}

class OutscraperClient {
    public $version = "4.2.5";
    private $api_url = "https://api.app.outscraper.com";
    private $api_headers;
    private $max_ttl = 60 * 60;
    private $requests_pause = 5;

    /**
     * @param string $api_key API KEY from https://app.outscraper.com/profile
     */
    public function __construct(?string $api_key = NULL, int $requests_pause = 5) {
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

    private function make_post_request(string $path, array $payload = []): array {
        $ch = curl_init();

        $json = json_encode($payload);

        curl_setopt($ch, CURLOPT_URL, "{$this->api_url}/{$path}");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_HTTPHEADER, array_merge(
            $this->api_headers,
            ["Content-Type: application/json"]
        ));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);

        $result = json_decode(curl_exec($ch), true);

        if (curl_errno($ch)) {
            throw new Exception("API Error: " . curl_error($ch));
        }
        curl_close($ch);

        if (is_array($result) && array_key_exists("error", $result) && $result["error"] == TRUE) {
            throw new Exception($result["errorMessage"]);
        }

        return $result;
    }

    /**
     * Low-level POST helper (Node-like naming).
     *
     * @param string $path API path (e.g. "/businesses")
     * @param array $parameters JSON payload
     *
     * @return array decoded JSON response
     */
    public function postAPIRequest(string $path, array $parameters = []): array {
        $path = ltrim($path, '/');
        return $this->make_post_request($path, $parameters);
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
     * @param array $query Parameter defines the queries to search on Google (e.g., bitcoin, 37th president of usa). Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $pages_per_query Parameter specifies the limit of pages to return from one query.
     * @param string $uule Google UULE parameter is used to encode a place or an exact location (with latitude and longitude) into a code. By using it you can see a Google result page like someone located at the specified location.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param bool $webhook Parameter defines the URL address (callback) to which Outscraper will create a POST request with a JSON body once a task/request is finished. Using this parameter overwrites the webhook from integrations.
     *
     * @return array request/task result
     */
    public function google_search(
        array $query, int $pages_per_query = 1, string $uule = "", string $language = "en", ?string $region = NULL, ?string $webhook = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "pagesPerQuery" => $pages_per_query,
            "uule" => $uule,
            "language" => $language,
            "region" => $region,
            "webhook" => $webhook,
        ));
        $result = $this->make_get_request("google-search-v3?{$params}");
        return $result["data"];
    }

    /**
     * Fetches search results from Google News based on a given search query.
     *
     * @param array $query Parameter defines the queries to search on Google News (e.g., bitcoin, 37th president of usa). Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $pages_per_query Parameter specifies the limit of pages to return from one query.
     * @param string $uule Google UULE parameter is used to encode a place or an exact location (with latitude and longitude) into a code. By using it you can see a Google result page like someone located at the specified location.
     * @param string $tbs Google TBS parameter is used to search for news in Google News by specific time range. For example, `tbs=qdr:h` will search for news in the last hour.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param bool $async_request Parameter defines whether to run the request in an asynchronous manner. When set to true, the request will be processed in the background and the response will return a task id to check the status of the request.
     *
     * @return array request/task result
     */
    public function google_search_news(
        array $query, int $pages_per_query = 1, string $uule = "", string $tbs = "", string $language = "en",
        ?string $region = NULL, bool $async_request = FALSE
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "pagesPerQuery" => $pages_per_query,
            "uule" => $uule,
            "tbs" => $tbs,
            "language" => $language,
            "region" => $region,
            "async" => $async_request,
        ));
        $result = $this->make_get_request("google-search-news?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Get data from Google Maps (speed optimized endpoint)
     *
     * @param array $query Parameter defines queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param bool $extract_contacts Parameter specifies whether the bot will scrape additional data (emails, social links, site keywords…) from companies’ websites. It increases the time of the extraction.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param bool $drop_duplicates Parameter specifies whether the bot will drop the same organizations from different queries. Using the parameter combines results from each query inside one big array.
     * @param int $skip Skip first N places, where N should be multiple to 20 (e.g. 0, 20, 40). It's commonly used in pagination.
     * @param bool $async_request Parameter defines the way you want to submit your task to Outscraper. It can be set to `False` (default) to send a task and wait until you got your results, or `True` to submit your task and retrieve the results later using a request ID with `get_request_archive`. Each response is available for `2` hours after a request has been completed.
     * @param bool $webhook Parameter defines the URL address (callback) to which Outscraper will create a POST request with a JSON body once a task/request is finished. Using this parameter overwrites the webhook from integrations.
     *
     * @return array request/task result
     */
    public function google_maps_search(
        array $query, string $language = "en", ?string $region = NULL, int $limit = 400,
        ?string $coordinates = NULL, bool $drop_duplicates = FALSE, int $skip = 0, bool $async_request = FALSE, ?string $webhook = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "coordinates" => $coordinates,
            "dropDuplicates" => $drop_duplicates,
            "skipPlaces" => $skip,
            "async" => $async_request,
            "webhook" => $webhook,
        ));
        $result = $this->make_get_request("maps/search-v2?{$params}");

        if($async_request)
            return $result;

        return $result["data"];
    }

    /**
     * Get data from Google Maps
     *
     * @param array $query Parameter defines queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
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
        array $query, string $language = "en", ?string $region = NULL, int $limit = 400,
        bool $extract_contacts = FALSE, ?string $coordinates = NULL, bool $drop_duplicates = FALSE
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
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
     * Perform a search on Google Maps using the optimized v3 endpoint.
     *
     * @param array $query Array of search queries for Google Maps.
     * @param int $limit Maximum number of results per query. Default is 20.
     * @param string $language Language for Google Maps results. Default is "en".
     * @param string|null $region Optional region code to specify the search region.
     * @param int $skip Number of places to skip for pagination. Must be a multiple of 20.
     * @param bool $drop_duplicates Whether to remove duplicate results across queries.
     * @param array|null $enrichment Optional additional data enrichment options.
     * @param bool $async_request If true, returns immediately with a task ID for asynchronous processing.
     *
     * @return array The search results or task ID if asynchronous request is enabled.
     */
    public function google_maps_search_v3(
        array $query, int $limit = 20, string $language = "en", ?string $region = NULL, int $skip = 0,
        bool $drop_duplicates = FALSE, ?array $enrichment = NULL, bool $async_request = TRUE
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "skipPlaces" => $skip,
            "dropDuplicates" => $drop_duplicates,
            "enrichment" => $enrichment,
            "async" => $async_request,
        ));
        $result = $this->make_get_request("maps/search-v3?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Perform a directions search on Google Maps.
     *
     * @param string|array query Directions query or list of queries. Accepted formats: string: "<origin>    <destination>" (4 spaces between origin and destination); array of strings: each string in the same "<origin>    <destination>" format.
     * @param string|array $origin The starting point for directions. Can be an address, a latitude/longitude pair, or a place ID.
     * @param string|array $destination The ending point for directions. Can be an address, a latitude/longitude pair, or a place ID.
     * @param string $departure_time The desired departure time for the directions. Must be in the format "YYYY-MM-DDTHH:MM:SS".
     * @param string $finish_time The desired finish time for the directions. Must be in the format "YYYY-MM-DDTHH:MM:SS".
     * @param string $interval The interval for repeated directions. Must be in the format "YYYY-MM-DDTHH:MM:SS".
     * @param string $travel_mode The travel mode to use for directions. Can be "best", "driving", "walking", "bicycling", or "transit".
     * @param string $language The language to use for directions. Can be a two-letter ISO 639-1 language code.
     * @param string $region The region to use for directions. Can be a two-letter ISO 3166-1 country code.
     * @param array $fields The fields to include in the directions response. Can be an array of strings.
     * @param bool $async_request If true, returns immediately with a task ID for asynchronous processing.
     *
     * @return array The directions results or task ID if asynchronous request is enabled.
     */
    public function google_maps_directions(
        string|array $query,
        ?string $departure_time = null,
        ?string $finish_time = null,
        ?string $interval = null,
        string $travel_mode = 'best',
        string $language = 'en',
        ?string $region = null,
        ?array $fields = null,
        bool $async_request = true
    ): array {
        $queries = format_direction_queries($query);
        $params = http_build_query([
            'query'         => $queries,
            'departure_time'=> $departure_time,
            'finish_time'   => $finish_time,
            'interval'      => $interval,
            'travel_mode'   => $travel_mode,
            'language'      => $language,
            'region'        => $region,
            'async'         => $async_request,
            'fields'        => $fields,
        ]);

        $result = $this->make_get_request("maps/directions?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Get reviews from Google Maps (speed optimized endpoint for real time data)
     *
     * @param array $query Parameter defines queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param int $reviews_limit Parameter specifies the limit of reviews to extract from one organization.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param int $start Parameter specifies the start timestamp value for reviews (newest review). The current timestamp is used when the value is not provided. Using the start parameter overwrites the sort parameter to newest. Therefore, the latest reviews will be at the beginning.
     * @param int $cutoff Parameter specifies the maximum timestamp value for reviews. Using the cutoff parameter overwrites sort parameter to newest. Using the cutoff parameter overwrites sort parameter to newest. Therefore, the latest reviews will be at the beginning.
     * @param int $cutoff_rating Parameter specifies the maximum (for lowest_rating sorting) or minimum (for highest_rating sorting) rating for reviews. Using the cutoffRating requires sorting to be set to "lowest_rating" or "highest_rating".
     * @param string $sort Parameter specifies one of the sorting types. Available values: "most_relevant", "newest", "highest_rating", "lowest_rating".
     * @param string $reviews_query Parameter specifies the query to search among the reviews (e.g. wow, amazing, horrible place).
     * @param bool $ignore_empty Parameter specifies whether to ignore reviews without text or not.
     * @param string $last_pagination_id Parameter specifies the review_pagination_id of the last item. It's commonly used in pagination.
     * @param bool $async_request Parameter defines the way you want to submit your task to Outscraper. It can be set to `False` (default) to send a task and wait until you got your results, or `True` to submit your task and retrieve the results later using a request ID with `get_request_archive`. Each response is available for `2` hours after a request has been completed.
     * @param bool $webhook Parameter defines the URL address (callback) to which Outscraper will create a POST request with a JSON body once a task/request is finished. Using this parameter overwrites the webhook from integrations.
     *
     * @return array request/task result
     */
    public function google_maps_reviews(
        array $query, string $language = "en", ?string $region = NULL, int $limit = 1,
        int $reviews_limit = 100, ?string $coordinates = NULL, ?int $start = NULL, ?int $cutoff = NULL, ?int $cutoff_rating = NULL,
        string $sort = "most_relevant", ?string $reviews_query = NULL, bool $ignore_empty = FALSE, ?string $source = NULL,
        ?string $last_pagination_id = NULL, bool $async_request = FALSE, ?string $webhook = NULL
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "reviewsPerOrganizationLimit" => $reviews_limit,
            "coordinates" => $coordinates,
            "start" => $start,
            "cutoff" => $cutoff,
            "cutoffRating" => $cutoff_rating,
            "sort" => $sort,
            "reviewsQuery" => $reviews_query,
            "ignoreEmpty" => $ignore_empty,
            "source" => $source,
            "lastPaginationId" => $last_pagination_id,
            "async" => $async_request,
            "webhook" => $webhook,
        ));
        $result = $this->make_get_request("maps/reviews-v3?{$params}");

        if($async_request)
            return $result;

        return $result["data"];
    }

    /**
     * Get reviews from Google Maps
     *
     * @param array $query Parameter defines queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param int $limit Parameter specifies the limit of organizations to take from one query search. Usually, there are no more than 400 organizations per one query search on Google Maps. Use more precise categories (asian restaurant, italian restaurant, etc.) to overcome this limitation.
     * @param int $reviews_limit Parameter specifies the limit of reviews to extract from one organization.
     * @param string $coordinates Parameter defines the coordinates to use along with the query. Example: "@41.3954381,2.1628662,15.1z".
     * @param int $cutoff Parameter specifies the maximum timestamp value for reviews. Using the cutoff parameter overwrites sort parameter to newest.
     * @param int $cutoff_rating Parameter specifies the maximum (for lowest_rating sorting) or minimum (for highest_rating sorting) rating for reviews. Using the cutoffRating requires sorting to be set to "lowest_rating" or "highest_rating".
     * @param string $sort Parameter specifies one of the sorting types. Available values: "most_relevant", "newest", "highest_rating", "lowest_rating".
     * @param string $reviews_query Parameter specifies the query to search among the reviews (e.g. wow, amazing, horrible place).
     * @param bool $ignore_empty Parameter specifies whether to ignore reviews without text or not.
     * @param bool $source (str): parameter specifies source filter. This commonly used for hotels where you can find reviews from other sources like Booking.com, Expedia, etc.
     *
     * @return array request/task result
     */
    public function google_maps_reviews_v2(
        array $query, string $language = "en", ?string $region = NULL, int $limit = 1,
        int $reviews_limit = 100, ?string $coordinates = NULL, ?int $cutoff = NULL, ?int $cutoff_rating = NULL,
        string $sort = "most_relevant", ?string $reviews_query = NULL, bool $ignore_empty = FALSE
    ) : array {
        $params = http_build_query(array(
            "query" => $query,
            "language" => $language,
            "region" => $region,
            "organizationsPerQueryLimit" => $limit,
            "reviewsPerOrganizationLimit" => $reviews_limit,
            "coordinates" => $coordinates,
            "cutoff" => $cutoff,
            "cutoffRating" => $cutoff_rating,
            "reviewsQuery" => $reviews_query,
            "ignoreEmpty" => $ignore_empty,
            "sort" => $sort
        ));
        $result = $this->make_get_request("maps/reviews-v2?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Get photos from Google Maps (speed optimized endpoint for real time data)
     *
     * @param string|array $query Parameter defines queries you want to search on Google Maps. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $photos_limit Parameter specifies the maximum number of photos per place. Available values: 0-100. Default: 100.
     * @param int $limit Parameter specifies the maximum number of places. Available values: 1-100. Default: 1.
     * @param string $tag Parameter specifies the tag to filter photos by. Available values: "all", "profile", "cover", "avatar". Default: "all".
     * @param string $language Parameter specifies the language to use for Google. Available values: "en", "de", "es", "es-419", "fr", "hr", "it", "nl", "pl", "pt-BR", "pt-PT", "vi", "tr", "ru", "ar", "th", "ko", "zh-CN", "zh-TW", "ja", "ach", "af", "ak", "ig", "az", "ban", "ceb", "xx-bork", "bs", "br", "ca", "cs", "sn", "co", "cy", "da", "yo", "et", "xx-elmer", "eo", "eu", "ee", "tl", "fil", "fo", "fy", "gaa", "ga", "gd", "gl", "gn", "xx-hacker", "ht", "ha", "haw", "bem", "rn", "id", "ia", "xh", "zu", "is", "jw", "rw", "sw", "tlh", "kg", "mfe", "kri", "la", "lv", "to", "lt", "ln", "loz", "lua", "lg", "hu", "mg", "mt", "mi", "ms", "pcm", "no", "nso", "ny", "nn", "uz", "oc", "om", "xx-pirate", "ro", "rm", "qu", "nyn", "crs", "sq", "sk", "sl", "so", "st", "sr-ME", "sr-Latn", "su", "fi", "sv", "tn", "tum", "tk", "tw", "wo", "el", "be", "bg", "ky", "kk", "mk", "mn", "sr", "tt", "tg", "uk", "ka", "hy", "yi", "iw", "ug", "ur", "ps", "sd", "fa", "ckb", "ti", "am", "ne", "mr", "hi", "bn", "pa", "gu", "or", "ta", "te", "kn", "ml", "si", "lo", "my", "km", "chr".
     * @param string $region Parameter specifies the region to use for Google. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO",
     * @param array $fields Parameter specifies which fields to return. Available values: "name", "address", "categories", "photos", "reviews", "type", "url", "website", "place_id", "phone", "latitude", "longitude", "rating", "working_hours", "images".
     * @param bool $async Parameter specifies whether to run the request in the background or not. Default: TRUE.
     * @param bool $ui Parameter specifies whether to return the full request or only the id. Default: FALSE.
     * @param string $webhook Parameter specifies the webhook to call when the request is finished.
     *
     * @return array request/task result
     */
    public function get_google_maps_photos(
        string|array $query,
        int $photos_limit = 100,
        int $limit = 1,
        string $tag = 'all',
        string $language = 'en',
        ?string $region = null,
        ?array $fields = null,
        bool $async = true,
        bool $ui = false,
        ?string $webhook = null
    ): array {
        $params = http_build_query([
            'query'       => (array) $query,
            'photosLimit' => $photos_limit,
            'limit'       => $limit,
            'tag'         => $tag,
            'language'    => $language,
            'region'      => $region,
            'fields'      => $fields,
            'async'       => $async,
            'ui'          => $ui,
            'webhook'     => $webhook,
        ]);

        $result = $this->make_get_request("maps/photos-v3?{$params}");

        if ($async) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve reviews from Google Play.
     *
     * @param string|array $query The query or queries to search for reviews on Google Play.
     * @param int $reviews_limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $sort The sorting order of the reviews. Options include 'most_relevant', 'newest', etc. Default is 'most_relevant'.
     * @param string|null $cutoff Optional parameter to specify the maximum timestamp for reviews.
     * @param int|null $rating Optional parameter to filter reviews by rating.
     * @param string $language The language for the reviews. Default is 'en'.
     * @param array|null $fields Optional parameter to specify which fields to return.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function google_play_reviews(
        string|array $query,
        int $reviews_limit = 100,
        string $sort = 'most_relevant',
        ?string $cutoff = null,
        ?int $rating = null,
        string $language = 'en',
        ?array $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'    => (array) $query,
            'limit'    => $reviews_limit,
            'sort'     => $sort,
            'cutoff'   => $cutoff,
            'rating'   => $rating,
            'language' => $language,
            'async'    => $async_request,
            'fields'   => $fields,
        ]);

        $result = $this->make_get_request("google-play/reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Contacts and Leads Scraper.
     *
     * Returns emails, social links, phones, and other contacts from websites
     * based on domain names or URLs. Supports batching by sending arrays with
     * up to 250 queries and allows multiple queries to be sent in one request
     * to save on network latency time.
     *
     * @param string|array      $query                 Company domains or URLs
     *                                                (e.g. 'outscraper.com' or ['tesla.com', 'microsoft.com']).
     * @param string|array|null $fields                Defines which fields to include in each returned item.
     *                                                By default, all fields are returned.
     * @param bool              $async_request         The parameter defines the way you want to submit your task.
     *                                                When true, the request is submitted asynchronously and the
     *                                                method returns the task meta (ID). When false, the client
     *                                                waits for the archived result and returns the final data.
     * @param string|array|null $preferred_contacts    Contact roles you want to prioritize
     *                                                (e.g. 'influencers', 'technical', ['decision makers', 'sales']).
     * @param int               $contacts_per_company  Number of contacts to return per company. Default is 3.
     * @param int               $emails_per_contact    Number of email addresses to return per contact. Default is 1.
     * @param int               $skip_contacts         Number of contacts to skip (for pagination). Default is 0.
     * @param bool              $general_emails        Whether to include only general emails
     *                                                (info@, support@, etc.) or only non-general emails
     *                                                (paul@, john@, etc.). Default is false.
     * @param bool              $ui                    Execute as a UI task. On the API side this forces async mode.
     *                                                Default is false.
     * @param string|null       $webhook               URL for callback notifications when a task completes.
     *
     * @return array Request/task result. If $async_request is true, returns the task meta
     *               (with ID). If false, waits for completion and returns the archived result data.
     */
    public function contacts_and_leads(
        string|array $query,
        string|array|null $fields = null,
        bool $async_request = true,
        string|array|null $preferred_contacts = null,
        int $contacts_per_company = 3,
        int $emails_per_contact = 1,
        int $skip_contacts = 0,
        bool $general_emails = false,
        bool $ui = false,
        ?string $webhook = null
    ): array {
        $queries    = (array) $query;
        $wait_async = $async_request || count($queries) > 1;

        $params = http_build_query([
            'query'               => $queries,
            'fields'              => $fields,
            'async'               => $wait_async,
            'preferred_contacts'  => $preferred_contacts,
            'contacts_per_company'=> $contacts_per_company,
            'emails_per_contact'  => $emails_per_contact,
            'skip_contacts'       => $skip_contacts,
            'general_emails'      => $general_emails,
            'ui'                  => $ui,
            'webhook'             => $webhook,
        ]);

        $result = $this->make_get_request("contacts-and-leads?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Return email addresses, social links and phones from domains in seconds.
     *
     * @param array $query Domains or links (e.g., outscraper.com).
     *
     * @return array json result
     */
    public function emails_and_contacts(array $query) : array {
        $params = http_build_query(array(
            "query" => $query,
            "async" => FALSE,
        ));
        $result = $this->make_get_request("emails-and-contacts?{$params}");
        return $result["data"];
    }

    /**
     * Returns phones carrier data (name/type), validates phones, ensures messages deliverability.
     *
     * @param array $query Phone numbers (e.g., +1 281 236 8208).
     *
     * @return array json result
     */
    public function phones_enricher(array $query) : array {
        $params = http_build_query(array(
            "query" => $query,
            "async" => FALSE,
        ));
        $result = $this->make_get_request("phones-enricher?{$params}");
        return $result["data"];
    }

    /**
     * Returns data from Amazon product pages.
     *
     * @param string|array $query The query or queries to search for products on Amazon.
     * @param int $limit The maximum number of products to retrieve. Default is 24.
     * @param string $domain The domain to search on. Default is 'amazon.com'.
     * @param string $postal_code The postal code to search on. Default is '11201'.
     * @param array|null $fields Optional parameter to specify which fields to return.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the products request or the request ID if async_request is true.
     */
    public function amazon_products(
        string|array $query,
        int $limit = 24,
        string $domain = 'amazon.com',
        string $postal_code = '11201',
        ?array $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'       => (array) $query,
            'limit'       => $limit,
            'domain'      => $domain,
            'postal_code' => $postal_code,
            'async'       => $async_request,
            'fields'      => $fields,
        ]);

        $result = $this->make_get_request("amazon/products-v2?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Returns reviews from Amazon product pages.
     *
     * @param string|array $query The query or queries to search for reviews on Amazon.
     * @param int $limit The maximum number of reviews to retrieve. Default is 10.
     * @param string $sort The sorting order of the reviews. Options include 'helpful', 'newest', etc. Default is 'helpful'.
     * @param string $filter_by_reviewer The type of reviewer to filter reviews by. Options include 'all_reviews', 'verified_purchase', etc. Default is 'all_reviews'.
     * @param string $filter_by_star The star rating to filter reviews by. Options include 'all_stars', 'positive', etc. Default is 'all_stars'.
     * @param string|null $domain The domain to search on. Default is null.
     * @param array|null $fields Optional parameter to specify which fields to return.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function amazon_reviews(
        string|array $query,
        int $limit = 10,
        string $sort = 'helpful',
        string $filter_by_reviewer = 'all_reviews',
        string $filter_by_star = 'all_stars',
        ?string $domain = null,
        ?array $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'              => (array) $query,
            'limit'              => $limit,
            'sort'               => $sort,
            'filterByReviewer'   => $filter_by_reviewer,
            'filterByStar'       => $filter_by_star,
            'domain'             => $domain,
            'async'              => $async_request,
            'fields'             => $fields,
        ]);

        $result = $this->make_get_request("amazon/reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve Yelp search results.
     *
     * @param string|array $query The query or queries to search for on Yelp.
     * @param int $limit The maximum number of results to retrieve. Default is 100.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the search request or the request ID if async_request is true.
     */
    public function yelp_search(
        string|array $query,
        int $limit = 100,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'limit' => $limit,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("yelp-search?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve Yelp reviews.
     *
     * @param string|array $query The query or queries to search for on Yelp.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $cursor The pagination cursor to retrieve the next set of results. Default is empty string.
     * @param string $sort The sorting order of the reviews. Options include 'relevance_desc', 'rating_asc', etc. Default is 'relevance_desc'.
     * @param string $cutoff The time-based cutoff for the reviews. Options include 'week', 'month', 'quarter', etc. Default is empty string.
     * @param string|array|null $fields Optional parameter to specify which fields to return. Default is null.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function yelp_reviews(
        string|array $query,
        int $limit = 100,
        string $cursor = '',
        string $sort = 'relevance_desc',
        string $cutoff = '',
        string|array|null $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'  => (array) $query,
            'limit'  => $limit,
            'cursor' => $cursor,
            'sort'   => $sort,
            'cutoff' => $cutoff,
            'fields' => $fields,
            'async'  => $async_request,
        ]);

        $result = $this->make_get_request("yelp/reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve reviews from TripAdvisor.
     *
     * @param string|array $query The query or queries to search for on TripAdvisor.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function tripadvisor_reviews(
        string|array $query,
        int $limit = 100,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'limit' => $limit,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("tripadvisor-reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }


    /**
     * Retrieve reviews from AppStore.
     *
     * @param string|array $query The query or queries to search for on AppStore.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $sort The sorting order of the reviews. Options include 'mosthelpful', 'newest', etc. Default is 'mosthelpful'.
     * @param string|null $cutoff Optional parameter to specify the maximum timestamp for reviews.
     * @param string|array $fields Optional parameter to specify which fields to return.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function appstore_reviews(
        string|array $query,
        int $limit = 100,
        string $sort = 'mosthelpful',
        ?string $cutoff = null,
        string|array $fields = '',
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'  => (array) $query,
            'limit'  => $limit,
            'sort'   => $sort,
            'cutoff' => $cutoff,
            'fields' => $fields,
            'async'  => $async_request,
        ]);

        $result = $this->make_get_request("appstore/reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve YouTube comments.
     *
     * @param string|array $query The query or queries to search for on YouTube.
     * @param int $per_query The maximum number of comments to retrieve per query. Default is 100.
     * @param string $language The language to use for the search. Default is 'en'.
     * @param string $region The region to use for the search. Default is empty string.
     * @param string|array $fields Optional parameter to specify which fields to return. Default is empty string.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the comments request or the request ID if async_request is true.
     */
    public function youtube_comments(
        string|array $query,
        int $per_query = 100,
        string $language = 'en',
        string $region = '',
        string|array $fields = '',
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'    => (array) $query,
            'perQuery' => $per_query,
            'language' => $language,
            'region'   => $region,
            'fields'   => $fields,
            'async'    => $async_request,
        ]);

        $result = $this->make_get_request("youtube-comments?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieve reviews from G2.
     *
     * @param string|array $query The query or queries to search for on G2. Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $sort The sorting order of the reviews. Default is empty string.
     * @param string|null $cutoff Optional parameter to specify the maximum timestamp for reviews.
     * @param string|array|null $fields Optional parameter to specify which fields to return.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function g2_reviews(
        string|array $query,
        int $limit = 100,
        string $sort = '',
        ?string $cutoff = null,
        string|array|null $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'  => (array) $query,
            'limit'  => $limit,
            'sort'   => $sort,
            'cutoff' => $cutoff,
            'fields' => $fields,
            'async'  => $async_request,
        ]);

        $result = $this->make_get_request("g2/reviews?{$params}");

        if ($async_request) {
            return $result;
        }

        return $this->wait_request_archive($result['id']);
    }

    /**
     * Returns reviews from Trustpilot businesses.
     *
     * @param array $query Links to Trustpilot pages or domain names (e.g., outscraper.com, https://www.trustpilot.com/review/outscraper.com). Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $limit Parameter specifies the limit of items to get from one query.
     * @param string $sort Parameter specifies one of the sorting types (e.g., recency).
     * @param int $cutoff Parameter specifies the oldest timestamp value for items. Using the cutoff parameter overwrites sort parameter`. Therefore, the latest records will be at the beginning (newest first).
     *
     * @return array request/task result
     */
    public function trustpilot_reviews(array $query, int $limit = 100, ?string $sort = NULL, ?int $cutoff = NULL) : array {
        $params = http_build_query(array(
            "query" => $query,
            "limit" => $limit,
            "sort" => $sort,
            "cutoff" => $cutoff,
        ));
        $result = $this->make_get_request("trustpilot/reviews?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Retrieve reviews from Glassdoor.
     *
     * @param string|array $query The query or queries to search for on Glassdoor.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $sort The sorting order of the reviews. Default is 'DATE'.
     * @param string|null $cutoff Optional parameter to specify the maximum timestamp for reviews.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function glassdoor_reviews(
        string|array $query,
        int $limit = 100,
        string $sort = 'DATE',
        ?string $cutoff = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'limit' => $limit,
            'sort'  => $sort,
            'cutoff'=> $cutoff,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("glassdoor/reviews?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }
    /**
     * Retrieve reviews from Capterra.
     *
     * @param string|array $query The query or queries to search for on Capterra.
     * @param int $limit The maximum number of reviews to retrieve. Default is 100.
     * @param string $sort The sorting order of the reviews. Default is empty string.
     * @param string|null $cutoff Optional parameter to specify the maximum timestamp for reviews.
     * @param string $language The language for the reviews. Default is 'en'.
     * @param string|null $region Optional parameter to filter reviews by region. Default is null.
     * @param string|array|null $fields Optional parameter to specify which fields to return. Default is null.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reviews request or the request ID if async_request is true.
     */
    public function capterra_reviews(
        string|array $query,
        int $limit = 100,
        string $sort = '',
        ?string $cutoff = null,
        string $language = 'en',
        ?string $region = null,
        string|array|null $fields = null,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query'    => (array) $query,
            'limit'    => $limit,
            'sort'     => $sort,
            'cutoff'   => $cutoff,
            'language' => $language,
            'region'   => $region,
            'fields'   => $fields,
            'async'    => $async_request,
        ]);

        $result = $this->make_get_request("capterra-reviews?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieves geocoding data based on provided query.
     *
     * @param string|array $query The query or queries to search for geocoding information.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the geocoding request or the request ID if async_request is true.
     */
    public function geocoding(
        string|array $query,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("geocoding?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieves reverse geocoding data based on provided query.
     *
     * @param string|array $query The query or queries to search for reverse geocoding information.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the reverse geocoding request or the request ID if async_request is true.
     */
    public function reverse_geocoding(
        string|array $query,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("reverse-geocoding?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieves phone identity finder data based on provided query.
     *
     * @param string|array $query The query or queries to search for phone identity finder information.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the phone identity finder request or the request ID if async_request is true.
     */
    public function phone_identity_finder(
        string|array $query,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("whitepages-phones?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieves address data from Whitepages.
     *
     * @param string|array $query The query or queries to search for address information.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the address scraper request or the request ID if async_request is true.
     */
    public function address_scraper(
        string|array $query,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("whitepages-addresses?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Retrieves company insights data based on provided query.
     *
     * @param string|array $query The query or queries to search for company insights information.
     * @param string|array $fields Optional parameter to specify which fields to return. Default is empty string.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     * @param array $enrichments Optional parameter to specify which enrichments to apply. Default is empty array.
     *
     * @return array The result of the company insights request or the request ID if async_request is true.
     */
    public function company_insights(
        string|array $query,
        string|array $fields = '',
        bool $async_request = false,
        array $enrichments = []
    ): array {
        $params = http_build_query([
            'query'       => (array) $query,
            'fields'      => $fields,
            'enrichments' => $enrichments,
            'async'       => $async_request,
        ]);

        $result = $this->make_get_request("company-insights?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Validates email addresses.
     *
     * @param string|array $query The query or queries to search for email validation information.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array The result of the email validation request or the request ID if async_request is true.
     */
    public function validate_emails(
        string|array $query,
        bool $async_request = false
    ): array {
        $params = http_build_query([
            'query' => (array) $query,
            'async' => $async_request,
        ]);

        $result = $this->make_get_request("email-validator?{$params}");

        return $async_request ? $result : $this->wait_request_archive($result['id']);
    }

    /**
     * Returns data from Trustpilot businesses.
     *
     * @param array $query Links to Trustpilot pages or domain names (e.g., outscraper.com, https://www.trustpilot.com/review/outscraper.com). Using an array allows multiple queries to be sent in one request and save on network latency time.
     *
     * @return array request/task result
     */
    public function trustpilot(array $query) : array {
        $params = http_build_query(array(
            "query" => $query,
        ));
        $result = $this->make_get_request("trustpilot?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Returns search resutls from Trustpilot.
     *
     * @param array $query Company or category to search on Trustpilot (e.g., real estate). Using an array allows multiple queries to be sent in one request and save on network latency time.
     * @param int $limit Parameter specifies the limit of items to get from one query.
     *
     * @return array request/task result
     */
    public function trustpilot_search(array $query, int $limit = 100) : array {
        $params = http_build_query(array(
            "query" => $query,
            "limit" => $limit,
        ));
        $result = $this->make_get_request("trustpilot/search?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Returns search resutls from Similarweb.
     *
     * @param string|array $query The query or queries to search for domain(website) information.
     * @param string|array $fields Optional parameter to specify which fields to return. Default is empty string.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array request/task result
     */
    public function similarweb(
        string|array $query,
        string|array $fields = '',
        bool $async_request = false
        ) : array {
        $params = http_build_query(array(
            "query" => $query,
            'fields'=> $fields,
            'async' => $async_request,
        ));
        $result = $this->make_get_request("similarweb?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Returns search resutls from Company Website Finder.
     *
     * @param string|array $query The query or queries to search for business names information.
     * @param string|array $fields Optional parameter to specify which fields to return. Default is empty string.
     * @param bool $async_request Whether to run the request asynchronously. Default is false.
     *
     * @return array request/task result
     */
    public function company_website_finder(
        string|array $query,
        string|array $fields = '',
        bool $async_request = false
        ) : array {
        $params = http_build_query(array(
            "query" => $query,
            'fields'=> $fields,
            'async' => $async_request,
        ));
        $result = $this->make_get_request("compamany-website-finder?{$params}");
        return $this->wait_request_archive($result["id"]);
    }

    /**
     * Returns search results from Yellow Pages.
     *
     * @param string|array $query Categories to search for (e.g., bars, restaurants, dentists). It supports batching by sending arrays with up to 250 queries (e.g., query=text1&query=text2&query=text3). It allows multiple queries to be sent in one request and to save on network latency time.
     * @param string $location The parameter specifies where to search (e.g., New York, NY). Default: "New York, NY".
     * @param int $limit The parameter specifies the limit of items to get from one query. Default: 100.
     * @param string|null $region The parameter specifies the country to use for website. It's recommended to use it for a better search experience. Available values: "AF", "AL", "DZ", "AS", "AD", "AO", "AI", "AG", "AR", "AM", "AU", "AT", "AZ", "BS", "BH", "BD", "BY", "BE", "BZ", "BJ", "BT", "BO", "BA", "BW", "BR", "VG", "BN", "BG", "BF", "BI", "KH", "CM", "CA", "CV", "CF", "TD", "CL", "CN", "CO", "CG", "CD", "CK", "CR", "CI", "HR", "CU", "CY", "CZ", "DK", "DJ", "DM", "DO", "EC", "EG", "SV", "EE", "ET", "FJ", "FI", "FR", "GA", "GM", "GE", "DE", "GH", "GI", "GR", "GL", "GT", "GG", "GY", "HT", "HN", "HK", "HU", "IS", "IN", "ID", "IQ", "IE", "IM", "IL", "IT", "JM", "JP", "JE", "JO", "KZ", "KE", "KI", "KW", "KG", "LA", "LV", "LB", "LS", "LY", "LI", "LT", "LU", "MG", "MW", "MY", "MV", "ML", "MT", "MU", "MX", "FM", "MD", "MN", "ME", "MS", "MA", "MQ", "MZ", "MM", "NA", "NR", "NP", "NL", "NZ", "NI", "NE", "NG", "NU", "MK", "NO", "OM", "PK", "PS", "PA", "PG", "PY", "PE", "PH", "PN", "PL", "PT", "PR", "QA", "RO", "RU", "RW", "WS", "SM", "ST", "SA", "SN", "RS", "SC", "SL", "SG", "SK", "SI", "SB", "SO", "ZA", "KR", "ES", "LK", "SH", "VC", "SR", "SE", "CH", "TW", "TJ", "TZ", "TH", "TL", "TG", "TO", "TT", "TN", "TR", "TM", "VI", "UG", "UA", "AE", "GB", "US", "UY", "UZ", "VU", "VE", "VN", "ZM", "ZW".
     * @param array|null $enrichment The parameter defines an enrichment or enrichments you want to apply to the results. Available values: "domains_service" (Emails & Contacts Scraper), "emails_validator_service" (Email Address Verifier), "company_websites_finder" (Company Website Finder), "disposable_email_checker" (Disposable Emails Checker), "company_insights_service" (Company Insights), "phones_enricher_service" (Phone Numbers Enricher), "trustpilot_service" (Trustpilot Scraper), "whitepages_phones" (Phone Identity Finder), "ai_chain_info" (Chain Info). Using enrichments increases the time of the response.
     * @param array $fields The parameter defines which fields you want to include with each item returned in the response. By default, it returns all fields.
     * @param bool $async_request The parameter defines the way you want to submit your task to Outscraper. It can be set to `False` to open an HTTP connection and keep it open until you got your results, or `True` (default) to just submit your requests to Outscraper and retrieve them later with the Request Results endpoint. Default: True.
     * @param bool|null $ui The parameter defines whether a task will be executed as a UI task. This is commonly used when you want to create a regular platform task with API. Using this parameter overwrites the async_request parameter to `True`. Default: False.
     * @param string|null $webhook The parameter defines the URL address (callback) to which Outscraper will create a POST request with a JSON body once a task/request is finished. Using this parameter overwrites the webhook from integrations.
     *
     * @return array JSON result
     */
    public function yellowpages_search(
        string|array $query,
        string $location = 'New York, NY',
        int $limit = 100,
        ?string $region = null,
        ?array $enrichment = null,
        ?array $fields = null,
        bool $async_request = true,
        ?bool $ui = null,
        ?string $webhook = null
    ) : array {
        $params = http_build_query(array(
            'query' => $query,
            'location' => $location,
            'limit' => $limit,
            'region' => $region,
            'enrichment' => $enrichment,
            'fields' => $fields,
            'async' => $async_request,
            'ui' => $ui,
            'webhook' => $webhook,
        ));

        $result = $this->make_get_request("yellowpages-search?{$params}");
        return $this->wait_request_archive($result['id']);
    }

    /**
     * POST /businesses
     *
     * Mirrors Node businessesSearch payload:
     *  - filters (array)
     *  - query (string|null)
     *  - limit (int)
     *  - include_total (bool)
     *  - cursor (string|null)
     *  - fields (array|null)
     *  - async (bool)
     *  - ui (bool)
     *  - webhook (string|null)
     */
    public function businessesSearch(
        array $filters = [],
        int $limit = 10,
        bool $include_total = false,
        ?string $cursor = null,
        ?array $fields = null,
        bool $async_request = false,
        bool $ui = false,
        ?string $webhook = null,
        ?string $query = null
    ): array {
        $payload = array_filter([
            'filters' => $filters ?: (object)[],
            'query' => $query,
            'limit' => $limit,
            'include_total' => $include_total,
            'cursor' => $cursor,
            'fields' => $fields,
            'async' => $async_request,
            'ui' => $ui,
            'webhook' => $webhook,
        ], fn($v) => $v !== null && $v !== '');

        $result = $this->make_post_request("businesses", $payload);

        return $result;
    }

    /**
     * Auto-pagination for /businesses (like Node businessesIterSearch),
     * returns merged "items" array (all businesses across pages).
     */
    public function businessesIterSearch(
        array $filters = [],
        int $limit = 10,
        ?array $fields = null,
        bool $include_total = false
    ): array {
        $cursor = null;
        $all_items = [];

        while (true) {
            $page = $this->businessesSearch(
                $filters,
                $limit,
                $include_total,
                $cursor,
                $fields,
                false
            );

            $items = $page['items'] ?? [];
            if (!is_array($items) || count($items) === 0) {
                break;
            }

            $all_items = array_merge($all_items, $items);

            $has_more = (bool)($page['has_more'] ?? false);
            $next_cursor = $page['next_cursor'] ?? null;

            if (!$has_more || !$next_cursor) {
                break;
            }

            $cursor = (string)$next_cursor;
        }

        return $all_items;
    }

    /**
     * GET /businesses/{business_id}
     *
     * @param string $business_id Outscraper ID (or whichever id your API expects in path)
     * @param string|array|null $fields Fields to request (array -> comma-separated, like JS)
     * @param bool $async_request
     * @param bool $ui
     * @param string|null $webhook
     */
    public function businessesGet(
        string $business_id,
        string|array|null $fields = null,
        bool $async_request = false,
        bool $ui = false,
        ?string $webhook = null
    ): array {
        if (!$business_id) {
            throw new Exception("business_id must have a value");
        }

        $fields_param = $fields;
        if (is_array($fields)) {
            $fields_param = implode(',', $fields);
        }

        $params = http_build_query(array_filter([
            'fields' => $fields_param,
            'async' => $async_request,
            'ui' => $ui,
            'webhook' => $webhook,
        ], fn($v) => $v !== null));

        $encoded_id = rawurlencode($business_id);

        $result = $this->make_get_request("businesses/{$encoded_id}?{$params}");

        return $result;
    }
}

?>
