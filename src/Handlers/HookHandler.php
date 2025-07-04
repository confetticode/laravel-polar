<?php

namespace ConfettiCode\LaravelPolar\Hooks;

interface HookHandler
{
    /**
     * Handle the webhook payload.
     *
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload);
}
