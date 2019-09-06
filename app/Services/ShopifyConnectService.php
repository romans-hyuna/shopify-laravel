<?php

namespace App\Services;
use Illuminate\Support\Str;

class ShopifyConnectService extends BaseSopifyService
{
    /**
     * @var array
     */
    private $scopes = [
        'write_orders',
        'read_customers'
    ];

    /**
     * Redirect user to shopify page
     *
     * @param string $domain
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirectToShopify(string $domain)
    {
        return redirect($this->getAuthorizeUrl($domain));
    }

    /**
     * Handle shopify ouath callback
     *
     * @param array $request
     * @return mixed
     */
    public function handleCallback(array $request)
    {
        $this->validateRequest($request);

        $response = $this->client->post($this->getTokenUrl($request['shop']),
            [
                'form_params' => [
                    'code' => $request['code'],
                    'client_id' => $this->config['client_id'],
                    'client_secret' => $this->config['client_secret']
                ]
            ]
        )->getBody();

        return json_decode($response);
    }

    /**
     * Return auth token url
     *
     * @param string $domain
     * @return string
     */
    public function getTokenUrl(string $domain)
    {
        return $this->getApiUrl($domain) . "/oauth/access_token";
    }

    /**
     * Return authirize url with all params
     *
     * @param string $domain
     * @return string
     */
    public function getAuthorizeUrl(string $domain)
    {
        $client_id = $this->config['client_id'];
        $redirect_url = $this->config['redirect_url'];
        $scopes = $this->getScopes();
        $state = $this->getState();

        return $this->getApiUrl($domain) . "/oauth/authorize?client_id=$client_id&scope=$scopes&redirect_uri=$redirect_url&state=$state";
    }

    /**
     * Validate request from shopify
     *
     * @param array $request
     * @return void
     */
    public function validateRequest(array $request)
    {
        if (empty($request['state']) || $request['state'] != $this->getState()) {
            throw new \InvalidArgumentException('State is invalid');
        }

        if (empty($request['shop'])) {
            throw new \InvalidArgumentException('Shop is invalid');
        }

        if (empty($request['hmac']) || !$this->verifyHmacAppInstall($request)) {
            throw new \InvalidArgumentException('Hmac is invalid');
        }
    }

    /**
     * Check if hmac is correct
     *
     * @param $request
     * @return bool
     */
    public function verifyHmacAppInstall($request) {
        $params = [];

        foreach($request as $param => $value) {
            if ($param != 'signature' && $param != 'hmac') {
                $params[$param] = "{$param}={$value}";
            }
        }

        asort($params);

        $params = implode('&', $params);
        $hmac = $request['hmac'];
        $calculatedHmac = hash_hmac('sha256', $params, $this->config['client_secret']);

        return ($hmac == $calculatedHmac);
    }

    /**
     * Add scopes to authirize url
     *
     * @param array $scopes
     * @return $this
     */
    public function scopes(array $scopes)
    {
        $this->scopes = array_unique(array_merge($this->scopes, (array) $scopes));

        return $this;
    }

    /**
     * Get all scopes
     *
     * @return string
     */
    public function getScopes()
    {
        return implode(',', $this->scopes);
    }

    /**
     * Get state for authorize url
     *
     * @return \Illuminate\Session\SessionManager|\Illuminate\Session\Store|mixed|string
     */
    public function getState()
    {
        if (session('state')) {
            return session('state');
        }

        $state = Str::random(40);
        session()->put('state', $state);

        return $state;
    }
}