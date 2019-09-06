<?php

namespace App\Services;

use GuzzleHttp\Client;


class BaseSopifyService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var \Illuminate\Config\Repository|mixed
     */

    protected $config;
    /**
     * BaseSopifyService constructor.s
     */
    public function __construct()
    {
        $this->config = config('services.shopify');
        $this->client = new Client();
    }

    /**
     * Get base shopify api url
     *
     * @param string $domain
     * @return string
     */
    public function getApiUrl(string $domain)
    {
        return "https://$domain/admin";
    }
}