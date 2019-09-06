<?php

namespace App\Services;

use Illuminate\Support\Str;

class ShopifyWebhookService extends BaseSopifyService
{
    /**
     * Check if hmac is valids
     *
     * @param $payload
     * @param $hmac
     * @return bool
     */
    public function validateHmac($payload ,$hmac)
    {
        if (empty($hmac)) {
            return false;
        }

        $calculated_hmac = base64_encode(hash_hmac('sha256', $payload, $this->config['client_secret'], true));
        return hash_equals($hmac, $calculated_hmac);
    }

    /**
     * Handle app uninstall webhook
     *
     * @param $payload
     * @return bool
     */
    public function handleAppUninstalled($payload)
    {
        info('Process uninstall app here', [$payload]);
        return true;
    }

    /**
     * Get method name depends weobhook name
     *
     * @param $topic
     * @return string
     */
    public function getWebhookMethodName($topic)
    {
        return 'handle' . Str::studly((str_replace("/", '_', $topic)));
    }
}