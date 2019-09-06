<?php
/**
 * Created by PhpStorm.
 * User: Рома
 * Date: 06.09.2019
 * Time: 0:21
 */

namespace App\Services;


class ShopifyAPIService extends BaseSopifyService
{
    /**
     * Get all currencies from shopify api
     *
     * @param string $domain
     * @return mixed
     */
    public function getShopInfo(string $domain)
    {
        $res = $this->client->get($this->getApiUrl($domain) . '/api/2019-07/shop.json?fields=timezone,currency', [
            'headers' => $this->getHeaders()
        ])->getBody();

        return json_decode($res);
    }

    /**
     * Create weebhook on shopify api
     *
     * @param string $domain
     * @param string $name
     * @return mixed
     */
    public function createWebhook(string $domain, string $name)
    {
        $res = $this->client->post($this->getApiUrl($domain) . '/api/2019-07/webhooks.json', [
            'form_params' => [
                'webhook' => [
                    'topic' => $name,
                    'format' => 'json',
                    'address' => $this->config['webhook_url']

                ],
            ],
            'headers' => $this->getHeaders()
        ])->getBody();

        return json_decode($res);
    }

    /**
     * Get default headers for api connect
     *
     * @return array
     */
    public function getHeaders()
    {
        return [
            'X-Shopify-Access-Token' => $this->getToken(),
        ];
    }

    /**
     * Get auth token for api call
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed
     */
    public function getToken()
    {
        if(empty(session('token'))) {
            throw new \InvalidArgumentException('Token is empty');
        }

        return session('token');
    }
}