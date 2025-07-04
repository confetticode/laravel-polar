<?php

namespace ConfettiCode\LaravelPolar\Handlers;

interface HookHandler
{
    /**
     * Handle the webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload);
}
