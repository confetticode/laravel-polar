<?php

namespace ConfettiCode\LaravelPolar\Jobs;

use ConfettiCode\LaravelPolar\Polar;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessWebhook extends ProcessWebhookJob
{
    public function handle(Polar $polar): void
    {
        $polar->handleWebhookCall($this->webhookCall);
    }
}
