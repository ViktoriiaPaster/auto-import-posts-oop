<?php

namespace AutoImport;

if (!defined('API_KEY')) {
    define('API_KEY', '');
}

class FetchPosts
{
    private $api_key;

    public function __construct()
    {
        $this->api_key = defined('API_KEY') ? API_KEY : '';
    }

    public function fetchPosts()
    {
        $url = 'https://my.api.mockaroo.com/posts.json';

        $response = wp_remote_get($url, array(
            'headers' => array(
                'X-API-Key' => $this->api_key,
            )
        ));

        if (is_wp_error($response)) {
            $error_message = $response->get_error_message();
            error_log('API Error:' . $error_message);
            return [];
        }

        if (wp_remote_retrieve_response_code($response) !== 200) {
            error_log('API Error: ' . wp_remote_retrieve_body($response));
            return [];
        }

        $body = wp_remote_retrieve_body($response);
        $posts = json_decode($body, true);

        return $posts;
    }
}
