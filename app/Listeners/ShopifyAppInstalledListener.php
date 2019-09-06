<?php

namespace App\Listeners;

use App\Events\ShopifyAppInstalled;
use App\Jobs\CreateShopifyWebhook;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\Jobs\GetShopInfo;

class ShopifyAppInstalledListener implements ShouldQueue
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  ShopifyAppInstalled  $event
     * @return void
     */
    public function handle(ShopifyAppInstalled $event)
    {
        GetShopInfo::dispatch($event->domain);
        CreateShopifyWebhook::dispatch($event->domain);
    }
}
