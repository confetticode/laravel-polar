<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Polar API URL
    |--------------------------------------------------------------------------
    |
    | This url is where we send HTTP requests to the Polar system. The default
    | value should be the production one. For other environments, we can set
    | however we want, such as 'https://sandbox-api.polar.sh'.
    |
    */

    'api_url' => env('POLAR_API_URL', 'https://api.polar.sh'),

    /*
    |--------------------------------------------------------------------------
    | Polar Access Token
    |--------------------------------------------------------------------------
    |
    | The Polar access token is used to authenticate with the Polar API.
    | You can find your access token in the Polar dashboard > Settings
    | under the "Developers" section.
    |
    */
    'access_token' => env('POLAR_ACCESS_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Polar Webhook Secret
    |--------------------------------------------------------------------------
    |
    | The Polar webhook secret is used to verify that the webhook requests
    | are coming from Polar. You can find your webhook secret in the Polar
    | dashboard > Settings > Webhooks on each registered webhook.
    |
    | We (the developers) recommend using a single webhook for all your
    | integrations. This way you can use the same secret for all your
    | integrations and you don't have to manage multiple webhooks.
    |
    */
    'webhook_secret' => env('POLAR_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Polar Url Path
    |--------------------------------------------------------------------------
    |
    | This is the base URI where routes from Polar will be served
    | from. The URL built into Polar is used by default; however,
    | you can modify this path as you see fit for your application.
    |
    */
    'path' => env('POLAR_PATH', 'polar'),

    /*
    |--------------------------------------------------------------------------
    | Default Redirect URL
    |--------------------------------------------------------------------------
    |
    | This is the default redirect URL that will be used when a customer
    | is redirected back to your application after completing a purchase
    | from a checkout session in your Polar account.
    |
    */
    'redirect_url' => null,

    /*
    |--------------------------------------------------------------------------
    | Currency Locale
    |--------------------------------------------------------------------------
    |
    | This is the default locale in which your money values are formatted in
    | for display. To utilize other locales besides the default "en" locale
    | verify you have to have the "intl" PHP extension installed on the system.
    |
    */
    'currency_locale' => env('POLAR_CURRENCY_LOCALE', 'en'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Handlers
    |--------------------------------------------------------------------------
    */

    'hooks' => [
        'customer.updated' => \ConfettiCode\LaravelPolar\Hooks\CustomerHandler::class,
        'order.created' => \ConfettiCode\LaravelPolar\Hooks\OrderHandler::class,
        'order.updated' => \ConfettiCode\LaravelPolar\Hooks\OrderHandler::class,
        'subscription.created' => \ConfettiCode\LaravelPolar\Hooks\SubscriptionHandler::class,
        'subscription.updated' => \ConfettiCode\LaravelPolar\Hooks\SubscriptionHandler::class,
        'subscription.active' => \ConfettiCode\LaravelPolar\Hooks\SubscriptionHandler::class,
        'subscription.canceled' => \ConfettiCode\LaravelPolar\Hooks\SubscriptionHandler::class,
        'subscription.revoked' => \ConfettiCode\LaravelPolar\Hooks\SubscriptionHandler::class,
    ],

];
