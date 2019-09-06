<?php

namespace App\Jobs;

use App\Services\ShopifyAPIService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CreateShopifyWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $domain;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($domain)
    {
        $this->domain = $domain;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(ShopifyAPIService $shopifyAPIService)
    {
        info('Job CreateShopifyWebhook started');
        $webhook = $shopifyAPIService->createWebhook($this->domain, 'app/uninstalled');
        info('Job CreateShopifyWebhook finished', [$webhook]);
    }
}
