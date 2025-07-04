<?php

namespace ConfettiCode\LaravelPolar;

use Illuminate\Config\Repository;
use Spatie\WebhookClient\Models\WebhookCall;
use Illuminate\Contracts\Container\Container;

class Polar
{
    public function __construct(protected readonly Container $app, protected readonly Repository $config)
    {
        //
    }

    public function handleWebhookCall(WebhookCall $webhookCall)
    {
        $payload = $webhookCall->payload;
        $type = $payload['type'];

        $class = $this->config->get("polar.{$type}", null);

        if (is_null($class)) {
            $this->app->make('log')->info("Unsupported Polar webhook event: $type");
        } else {
            foreach ((array) $class as $abstract) {
                $this->app->make($abstract)->handle($payload);
            }
        }
    }
}
