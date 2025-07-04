<?php

namespace ConfettiCode\LaravelPolar;

use Illuminate\Config\Repository;
use Spatie\WebhookClient\Models\WebhookCall;
use Illuminate\Contracts\Container\Container;
use ConfettiCode\LaravelPolar\Handlers\HookHandler;

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

        $hooks = (array) $this->config->get("polar.hooks", []);

        $classes = (array) ($hooks[$type] ?? []);

        if (empty($classes)) {
            $this->app->make('log')->info("Unsupported Polar webhook event: $type");
        } else {
            foreach ((array) $classes as $abstract) {
                $handler = $this->app->make($abstract);

                if (! $handler instanceof HookHandler) {
                    throw new \RuntimeException('$handler must be an implementation of ' . HookHandler::class);
                }

                $handler->handle($payload);
            }
        }
    }
}
