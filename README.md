# Laravel Polar

[![Latest Version on Packagist](https://img.shields.io/packagist/v/confetticode/laravel-polar.svg?style=flat-square)](https://packagist.org/packages/confetticode/laravel-polar)
[![GitHub Tests Action Status](https://img.shields.io/github/actions/workflow/status/confetticode/laravel-polar/test.yml?branch=main&label=test&style=flat-square)](https://github.com/confetticode/laravel-polar/actions?query=workflow%3Atest+branch%3Amain)
[![GitHub Code Style Action Status](https://img.shields.io/github/actions/workflow/status/confetticode/laravel-polar/fix-php-code-style-issues.yml?branch=main&label=code%20style&style=flat-square)](https://github.com/confetticode/laravel-polar/actions?query=workflow%3A"Fix+PHP+code+style+issues"+branch%3Amain)
[![Total Downloads](https://img.shields.io/packagist/dt/confetticode/laravel-polar.svg?style=flat-square)](https://packagist.org/packages/confetticode/laravel-polar)

## ⚠️ Warning

This package is NOT an official Polar project. I first copied code from [danestves/laravel-polar](https://github.com/danestves/laravel-polar) but will modify the way I expect. Changes follow semantic versioning and are noted in [CHANGELOG.md](./CHANGELOG.md) file. For the official one, please visit [Polar for Laravel](https://docs.polar.sh/integrate/sdk/adapters/laravel).

## Requirements

Laravel `12.x`


## Installation

**Step 1:** Install the package via composer

```bash
composer require confetticode/laravel-polar
```

**Step 2:** Run the `polar:install` command

```bash
php artisan polar:install
```

This will publish the config, migrations and views, and ask to run the migrations.

Or publish and run the migrations individually:

```bash
php artisan vendor:publish --tag="polar-migrations"
php artisan vendor:publish --tag="polar-config"
php artisan vendor:publish --tag="polar-views"
php artisan migrate
```

**Step 3:** Set polar api url, access token and webhook secret

Create a new token in the Polar Dashboard > Settings > Developers

> E.g. https://polar.sh/dashboard/org_slug/settings

Create a new webhook secret in the Polar Dashboard > Settings > Webhooks

> E.g. https://polar.sh/dashboard/org_slug/settings/Webhooks

Then, set them in the .env file.

```bash
POLAR_API_URL=https://api.polar.sh
POLAR_ACCESS_TOKEN="<your_access_token>"
POLAR_WEBHOOK_SECRET="<your_webhook_secret>"
```
> For sandbox, use https://sandbox.polar.sh and https://sandbox-api.polar.sh

## Usage

### Billable Trait

Let’s make sure everything’s ready for your customers to checkout smoothly. 🛒

First, we’ll need to set up a model to handle billing—don’t worry, it’s super simple! In most cases, this will be your app’s User model. Just add the Billable trait to your model like this (you’ll import it from the package first, of course):

```php
use ConfettiCode\LaravelPolar\Billable;

class User extends Authenticatable
{
    use Billable;
}
```

Now the user model will have access to the methods provided by the package. You can make any model billable by adding the trait to it, not just the User model.

### Polar Script

Polar includes a JavaScript script that you can use to initialize the [Polar Embedded Checkout](https://docs.polar.sh/features/checkout/embed). If you going to use this functionality, you can use the `@polarEmbedScript` directive to include the script in your views inside the `<head>` tag.

```blade
<head>
    ...

    @polarEmbedScript
</head>
```

### Webhooks

This package includes a webhook handler that will handle the webhooks from Polar.

#### Webhooks & CSRF Protection

Incoming webhooks should not be affected by [CSRF protection](https://laravel.com/docs/csrf). To prevent this, add your webhook path to the except list:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->validateCsrfTokens(except: [
        'polar/*',
    ]);
})
```

### Commands

This package includes a list of commands that you can use to retrieve information about your Polar account.

| Command | Description |
|---------|-------------|
| `php artisan polar:products` | List all available products with their ids |
| `php artisan polar:products --id=123` | List a specific product by id |
| `php artisan polar:products --id=123 --id=321` | List a specific products by ids |

### Checkouts

#### Single Payments

To create a checkout to show only a single payment, pass a single items to the array of products when creating the checkout.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->checkout(['product_id_123']);
});
```

If you want to show multiple products that the user can choose from, you can pass an array of product ids to the `checkout` method.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->checkout(['product_id_123', 'product_id_456']);
});
```

This could be useful if you want to offer monthly, yearly, and lifetime plans for example.

> [!NOTE]
> If you are requesting the checkout a lot of times we recommend you to cache the URL returned by the `checkout` method.

#### Custom Price

You can override the price of a product using the `charge` method.

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->charge(1000, ['product_id_123']);
});
```

#### Embedded Checkout

Instead of redirecting the user you can create the checkout link, pass it to the page and use our blade component:

```php
use Illuminate\Http\Request;

Route::get('/billing', function (Request $request) {
    $checkout = $request->user()->checkout(['product_id_123']);

    return view('billing', ['checkout' => $checkout]);
});
```

Now we can use the button like this:

```blade
<x-polar-button :checkout="$checkout" />
```

The component accepts the normal props that a link element accepts. You can change the theme of the embedded checkout by using the following prop:

```blade
<x-polar-button :checkout="$checkout" data-polar-checkout-theme="dark" />
```

It defaults to light theme, so you only need to pass the prop if you want to change it.

##### Inertia

For projects usin Inertia you can render the button adding `data-polar-checkout` to the link in the following way:

`button.vue`
```vue
<template>
  <a href="<CHECKOUT_LINK>" data-polar-checkout>Buy now</a>
</template>
```

```jsx
// button.{jsx,tsx}

export function Button() {
  return (
    <a href="<CHECKOUT_LINK>" data-polar-checkout>Buy now</a>
  );
}
```

At the end is just a normal link but ysin an special attribute for the script to render the embedded checkout.

> [!NOTE]
> Remember that you can use the theme attribute too to change the color system in the checkout

### Prefill Customer Information

You can override the user data using the following methods in your models provided by the `Billable` trait.

```php
public function polarNameField(): string; // default: 'name'
public function polarEmailField(): string; // default: 'email'

public function polarName(): ?string; // default: $model->name (depends on polarNameField)
public function polarEmail(): ?string; // default: $model->email (depends on polarEmailField)
```

### Redirects After Purchase

You can redirect the user to a custom page after the purchase using the `withSuccessUrl` method:

```php
$request->user()->checkout('variant-id')
    ->withSuccessUrl(url('/success'));
```

You can also add the `checkout_id={CHECKOUT_ID}` query parameter to the URL to retrieve the checkout session id:

```php
$request->user()->checkout('variant-id')
    ->withSuccessUrl(url('/success?checkout_id={CHECKOUT_ID}'));
```

### Custom metadata and customer metadata

You can add custom metadata to the checkout session using the `withMetadata` method:

```php
$request->user()->checkout('variant-id')
    ->withMetadata(['key' => 'value']);
```

You can also add customer metadata to the checkout session using the `withCustomerMetadata` method:

```php
$request->user()->checkout('variant-id')
    ->withCustomerMetadata(['key' => 'value']);
```

These will then be available in the relevant webhooks for you.

#### Reserved Keywords

When working with custom data, this library has a few reserved terms.

- `billable_id`
- `billable_type`
- `subscription_type`

Using any of these will result in an exception being thrown.

### Customers

#### Customer Portal

Customers can update their personal information (e.g., name, email address) by accessing their [self-service customer portal](https://docs.polar.sh/features/customer-portal). To redirect customers to this portal, call the `redirectToCustomerPortal()` method on your billable model (e.g., the User model).

```php
use Illuminate\Http\Request;

Route::get('/customer-portal', function (Request $request) {
    return $request->user()->redirectToCustomerPortal();
});
```

Optionally, you can obtain the signed customer portal URL directly:

```php
$url = $user->customerPortalUrl();
```

### Orders

#### Retrieving Orders

You can retrieve orders by using the `orders` relationship on the billable model:

```blade
<table>
    @foreach ($user->orders as $order)
        <td>{{ $order->ordered_at->toFormattedDateString() }}</td>
        <td>{{ $order->polar_id }}</td>
        <td>{{ $order->amount }}</td>
        <td>{{ $order->tax_amount }}</td>
        <td>{{ $order->refunded_amount }}</td>
        <td>{{ $order->refunded_tax_amount }}</td>
        <td>{{ $order->currency }}</td>
        <!-- Add more columns as needed -->
    @endforeach
</table>
```

#### Check order status

You can check the status of an order by using the `status` attribute:

```php
$order->status;
```

Or you can use some of the helper methods offers by the `Order` model:

```php
$order->paid();
```

Aside from that, you can run two other checks: refunded, and partially refunded.  If the order is refunded, you can utilize the refunded_at timestamp:

```blade
@if ($order->refunded())
    Order {{ $order->polar_id }} was refunded on {{ $order->refunded_at->toFormattedDateString() }}
@endif
```

You may also see if an order was for a certain product:

```php
if ($order->hasProduct('product_id_123')) {
    // ...
}
```

Furthermore, you can check if a consumer has purchased a specific product:

```php
if ($user->hasPurchasedProduct('product_id_123')) {
    // ...
}
```

### Subscriptions

#### Creating Subscriptions

Starting a subscription is simple. For this, we require our product's variant id. Copy the product id and start a new subscription checkout using your billable model:

```php
use Illuminate\Http\Request;

Route::get('/subscribe', function (Request $request) {
    return $request->user()->subscribe('product_id_123');
});
```

When a customer completes their checkout, the incoming `SubscriptionCreated` event webhook connects it to your billable model in the database. You may then get the subscription from your billable model:

```php
$subscription = $user->subscription();
```

#### Checking Subscription Status

Once a consumer has subscribed to your services, you can use a variety of methods to check on the status of their subscription. The most basic example is to check if a customer has a valid subscription.

```php
if ($user->subscribed()) {
    // ...
}
```

You can utilize this in a variety of locations in your app, such as middleware, rules, and so on, to provide services. To determine whether an individual subscription is valid, you can use the `valid` method:

```php
if ($user->subscription()->valid()) {
    // ...
}
```

This method, like the subscribed method, returns true if your membership is active, on trial, past due, or cancelled during its grace period.

You may also check if a subscription is for a certain product:

```php
if ($user->subscription()->hasProduct('product_id_123')) {
    // ...
}
```

If you wish to check if a subscription is on a specific product while being valid, you can use:

```php
if ($user->subscribedToProduct('product_id_123')) {
    // ...
}
```

Alternatively, if you use different [subscription types](#multiple-subscriptions), you can pass a type as an additional parameter:

```php
if ($user->subscribed('swimming')) {
    // ...
}

if ($user->subscribedToProduct('product_id_123', 'swimming')) {
    // ...
}
```

#### Cancelled Status

To see if a user has cancelled their subscription, you can use the cancelled method:

```php
if ($user->subscription()->cancelled()) {
    // ...
}
```

When they are in their grace period, you can utilize the `onGracePeriod` check.

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

#### Past Due Status

If a recurring payment fails, the subscription will become past due.  This indicates that the subscription is still valid, but your customer's payments will be retried in two weeks.

```php
if ($user->subscription()->pastDue()) {
    // ...
}
```

#### Subscription Scopes

There are several subscription scopes available for querying subscriptions in specific states:

```php
// Get all active subscriptions...
$subscriptions = Subscription::query()->active()->get();

// Get all of the cancelled subscriptions for a specific user...
$subscriptions = $user->subscriptions()->cancelled()->get();
```

Here's all available scopes:

```php
Subscription::query()->incomplete();
Subscription::query()->incompleteExpired();
Subscription::query()->onTrial();
Subscription::query()->active();
Subscription::query()->pastDue();
Subscription::query()->unpaid();
Subscription::query()->cancelled();
```

#### Changing Plans

When a consumer is on a monthly plan, they may desire to upgrade to a better plan, alter their payments to an annual plan, or drop to a lower-cost plan. In these cases, you can allow them to swap plans by giving a different product id to the `swap` method:

```php
use App\Models\User;

$user = User::find(1);

$user->subscription()->swap('product_id_123');
```

This will change the customer's subscription plan, however billing will not occur until the next payment cycle. If you want to immediately invoice the customer, you can use the `swapAndInvoice` method instead.

```php
$user = User::find(1);

$user->subscription()->swapAndInvoice('product_id_123');
```

#### Multiple Subscriptions

In certain situations, you may wish to allow your consumer to subscribe to numerous subscription kinds.  For example, a gym may provide a swimming and weight lifting subscription.  You can let your customers subscribe to one or both.

To handle the various subscriptions, you can offer a type of subscription as the second argument when creating a new one:

```php
$user = User::find(1);

$checkout = $user->subscribe('product_id_123', 'swimming');
```

You can now always refer to this specific subscription type by passing the type argument when getting it:

```php
$user = User::find(1);

// Retrieve the swimming subscription type...
$subscription = $user->subscription('swimming');

// Swap plans for the gym subscription type...
$user->subscription('gym')->swap('product_id_123');

// Cancel the swimming subscription...
$user->subscription('swimming')->cancel();
```

#### Cancelling Subscriptions

To cancel a subscription, call the `cancel` method.

```php
$user = User::find(1);

$user->subscription()->cancel();
```

This will cause your subscription to be cancelled.  If you cancel your subscription in the middle of the cycle, it will enter a grace period, and the ends_at column will be updated.  The customer will continue to have access to the services offered for the duration of the period.  You may check the grace period by calling the `onGracePeriod` method:

```php
if ($user->subscription()->onGracePeriod()) {
    // ...
}
```

Polar does not offer immediate cancellation.  To resume a subscription while it is still in its grace period, use the resume method.

```php
$user->subscription()->resume();
```

When a cancelled subscription approaches the end of its grace period, it becomes expired and cannot be resumed.

#### Subscription Trials

> [!NOTE]
> Coming soon.

### Handling Webhooks

Polar can send webhooks to your app, allowing you to react. By default, this package handles the majority of the work for you. If you have properly configured webhooks, it will listen for incoming events and update your database accordingly. We recommend activating all event kinds so you may easily upgrade in the future.

#### Supported Events

Currently, we support a few events by default. They have their own handlers defined in the `config/polar.php` file.

- `customer.updated`
- `order.created`
- `order.updated`
- `subscription.created`
- `subscription.updated`
- `subscription.active`
- `subscription.canceled`
- `subscription.revoked`

You can modify however you want. Please be carefully before changing these default behavior or subscribed methods don't work as you expect.

#### Additional Events

E.g, if you want to handle more events like `checkout.created` and `checkout.updated`. First, you have to create a class like this.

```php
<?php

namespace App\Hooks;

use ConfettiCode\LaravelPolar\Hooks\AbstractHookHandler;

class CheckoutHandler extends AbstractHookHandler;
{
    public function handle(array $payload): void
    {
        if ($payload['type'] === 'checkout.created') {
            $this->handleCreated($payload['data']);
        } elseif ($payload['type'] === 'checkout.updated') {
            $this->handleUpdated($payload['data']);
        }
    }
}
```

Then, you have to define them the in `config/polar.php` file.

```php
return [
    'hooks' => [
        // others
        'checkout.created' => \App\Hooks\CheckoutHandler::class,
        'checkout.updated' => \App\Hooks\CheckoutHandler::class,
    ],
];
```

The [Polar documentation](https://docs.polar.sh/integrate/webhooks/events) includes an example payload.

## Roadmap

- [ ] Add support for trials
    Polar itself doesn't support trials, but we can manage them by ourselves.

## Testing

```bash
./vendor/bin/pest
```

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## Security Vulnerabilities

Please review [our security policy](../../security/policy) on how to report security vulnerabilities.

## Credits

- [laravel/cashier (Stripe)](https://github.com/laravel/cashier-stripe)
- [laravel/cashier (Paddle)](https://github.com/laravel/cashier-paddle)
- [lemonsqueezy/laravel](https://github.com/lmsqueezy/laravel)
- [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
