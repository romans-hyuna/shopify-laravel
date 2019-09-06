<?php

namespace App\Http\Controllers;

use App\Events\ShopifyAppInstalled;
use App\Http\Requests\ShopifyRedirectRequest;
use App\Jobs\GetShopInfo;
use App\Services\ShopifyConnectService;
use App\Services\ShopifyWebhookService;
use Illuminate\Http\Request;

class ShopifyController extends Controller
{
    /**
     * @var ShopifyConnectService
     */
    private $shopify_connect_service;

    /**
     * ShopifyController constructor.
     * @param ShopifyConnectService $service
     */
    public function __construct(ShopifyConnectService $service)
    {
        $this->shopify_connect_service = $service;
    }

    /**
     * Redirect to oauth authorize url
     *
     * @param ShopifyRedirectRequest $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function redirect(ShopifyRedirectRequest $request)
    {
        try {
            return $this->shopify_connect_service
                ->scopes(['read_products'])
                ->redirectToShopify($request->input('domain'));
        } catch (\Exception $e) {
            report($e);
            return redirect(route('index'))->withErrors('Error connect shopify.');
        }
    }

    /**
     * Handle oauth callback
     *
     * @param Request $request
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     */
    public function callback(Request $request)
    {
        try {
            $token = $this->shopify_connect_service->handleCallback($request->all());
            if (empty($token->access_token)) {
                throw new \InvalidArgumentException('Token is empty');
            }

            session()->put('token', $token->access_token);

            event(new ShopifyAppInstalled($request->input('shop')));
            //GetShopInfo::dispatch($request->input('shop'));

            return redirect(route('index'))->with('success', 'App connected');
        } catch (\Exception $e) {
            report($e);
            return redirect(route('index'))->withErrors('Error confirm shopify request');

        }
    }

    /**
     * Handle webhooks
     *
     * @param Request $request
     * @param ShopifyWebhookService $webhookService
     * @return \Illuminate\Http\JsonResponse
     */
    public function webhook(Request $request, ShopifyWebhookService $webhookService)
    {
        $payload = json_decode($request->getContent(), true);

        if (!$webhookService->validateHmac($payload, $request->header('x-shopify-hmac-sha256'))) {
            logger()->error("Wrong Hmac on webhook process");
            return response()->json('');
        }

        $webhok_method = $webhookService->getWebhookMethodName($request->header('x-shopify-topic'));

        if (!method_exists($webhookService, $webhok_method)) {
            logger()->error("Wrong method on webhook process");
            return response()->json('');
        }

        $webhookService->$webhok_method($payload);

        return response()->json('');
    }
}
