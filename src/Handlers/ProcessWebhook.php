<?php

namespace ConfettiCode\LaravelPolar\Handlers;

use Carbon\Carbon;
use ConfettiCode\LaravelPolar\Enums\OrderStatus;
use ConfettiCode\LaravelPolar\Events\BenefitGrantCreated;
use ConfettiCode\LaravelPolar\Events\BenefitGrantRevoked;
use ConfettiCode\LaravelPolar\Events\BenefitGrantUpdated;
use ConfettiCode\LaravelPolar\Events\CustomerUpdated;
use ConfettiCode\LaravelPolar\Events\OrderCreated;
use ConfettiCode\LaravelPolar\Events\OrderUpdated;
use ConfettiCode\LaravelPolar\Events\SubscriptionActive;
use ConfettiCode\LaravelPolar\Events\SubscriptionCanceled;
use ConfettiCode\LaravelPolar\Events\SubscriptionCreated;
use ConfettiCode\LaravelPolar\Events\SubscriptionRevoked;
use ConfettiCode\LaravelPolar\Events\SubscriptionUpdated;
use ConfettiCode\LaravelPolar\Events\WebhookHandled;
use ConfettiCode\LaravelPolar\Events\WebhookReceived;
use ConfettiCode\LaravelPolar\Exceptions\InvalidMetadataPayload;
use ConfettiCode\LaravelPolar\LaravelPolar;
use ConfettiCode\LaravelPolar\Order;
use ConfettiCode\LaravelPolar\Subscription;
use Illuminate\Support\Facades\Log;
use Spatie\WebhookClient\Jobs\ProcessWebhookJob;

class ProcessWebhook extends ProcessWebhookJob
{
    public function handle(): void
    {
        $decoded = json_decode($this->webhookCall, true);
        $payload = $decoded['payload'];
        $type = $payload['type'];
        $data = $payload['data'];

        WebhookReceived::dispatch($payload);

        match ($type) {
            'customer.updated' => $this->handleCustomerUpdated($data),
            'order.created' => $this->handleOrderCreated($data),
            'order.updated' => $this->handleOrderUpdated($data),
            'subscription.created' => $this->handleSubscriptionCreated($data),
            'subscription.updated' => $this->handleSubscriptionUpdated($data),
            'subscription.active' => $this->handleSubscriptionActive($data),
            'subscription.canceled' => $this->handleSubscriptionCanceled($data),
            'subscription.revoked' => $this->handleSubscriptionRevoked($data),
            'benefit_grant.created' => $this->handleBenefitGrantCreated($data),
            'benefit_grant.updated' => $this->handleBenefitGrantUpdated($data),
            'benefit_grant.revoked' => $this->handleBenefitGrantRevoked($data),
            default => Log::info("Unknown event type: $type"),
        };

        WebhookHandled::dispatch($payload);

        // Acknowledge you received the response
        http_response_code(200);
    }

    /**
     * Handle the customer.updated event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleCustomerUpdated(array $payload): void
    {
        $billable = $this->findOrCreateCustomer(
            $payload['metadata']['billable_id'],
            $payload['metadata']['billable_type'],
            $payload['id'],
        );

        $billable->update([
            $billable->polarEmailField() => $payload['email'],
            $billable->polarNameField() => $payload['name'],
        ]);

        CustomerUpdated::dispatch($billable, $payload);
    }

    /**
     * Handle the order created event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleOrderCreated(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        $order = $billable->orders()->create([ // @phpstan-ignore-line class.notFound - the property is found in the billable model
            'polar_id' => $payload['id'],
            'status' => $payload['status'],
            'amount' => $payload['amount'],
            'tax_amount' => $payload['tax_amount'],
            'refunded_amount' => $payload['refunded_amount'],
            'refunded_tax_amount' => $payload['refunded_tax_amount'],
            'currency' => $payload['currency'],
            'billing_reason' => $payload['billing_reason'],
            'customer_id' => $payload['customer_id'],
            'product_id' => $payload['product_id'],
            'ordered_at' => Carbon::make($payload['created_at']),
        ]);

        OrderCreated::dispatch($billable, $order, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the order updated event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleOrderUpdated(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        if (!($order = $this->findOrder($payload['id'])) instanceof LaravelPolar::$orderModel) {
            return;
        }

        $status = $payload['status'];
        $isRefunded = $status === OrderStatus::Refunded->value || $status === OrderStatus::PartiallyRefunded->value;

        $order->sync([
            ...$payload,
            'status' => $status,
            'refunded_at' => $isRefunded ? Carbon::make($payload['refunded_at']) : null,
            // Because the sync method require $attributes to have "ordered_at".
            'ordered_at' => Carbon::make($payload['created_at']),
        ]);

        OrderUpdated::dispatch($billable, $order, $payload, $isRefunded); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the subscription created event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleSubscriptionCreated(array $payload): void
    {
        $productMetadata = $payload['product']['metadata'];
        $billable = $this->resolveBillable($payload);

        $subscription = $billable->subscriptions()->create([ // @phpstan-ignore-line class.notFound - the property is found in the billable model
            'type' => $productMetadata['subscription_type'] ?? 'default',
            'polar_id' => $payload['id'],
            'status' => $payload['status'],
            'product_id' => $payload['product_id'],
            'current_period_end' => $payload['current_period_end'] ? Carbon::make($payload['current_period_end']) : null,
            'ends_at' => $payload['ends_at'] ? Carbon::make($payload['ends_at']) : null,
        ]);

        if ($billable->customer->polar_id === null) { // @phpstan-ignore-line property.notFound - the property is found in the billable model
            $billable->customer->update(['polar_id' => $payload['customer_id']]); // @phpstan-ignore-line property.notFound - the property is found in the billable model
        }

        SubscriptionCreated::dispatch($billable, $subscription, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the subscription updated event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleSubscriptionUpdated(array $payload): void
    {
        if (!($subscription = $this->findSubscription($payload['id'])) instanceof LaravelPolar::$subscriptionModel) {
            return;
        }

        $subscription->sync($payload);

        SubscriptionUpdated::dispatch($subscription->billable, $subscription, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the subscription active event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleSubscriptionActive(array $payload): void
    {
        if (!($subscription = $this->findSubscription($payload['id'])) instanceof LaravelPolar::$subscriptionModel) {
            return;
        }

        $subscription->sync($payload);

        SubscriptionActive::dispatch($subscription->billable, $subscription, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the subscription canceled event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleSubscriptionCanceled(array $payload): void
    {
        if (!($subscription = $this->findSubscription($payload['id'])) instanceof LaravelPolar::$subscriptionModel) {
            return;
        }

        $subscription->sync($payload);

        SubscriptionCanceled::dispatch($subscription->billable, $subscription, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the subscription revoked event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleSubscriptionRevoked(array $payload): void
    {
        if (!($subscription = $this->findSubscription($payload['id'])) instanceof LaravelPolar::$subscriptionModel) {
            return;
        }

        $subscription->sync($payload);

        SubscriptionRevoked::dispatch($subscription->billable, $subscription, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the benefit grant created event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleBenefitGrantCreated(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        BenefitGrantCreated::dispatch($billable, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the benefit grant updated event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleBenefitGrantUpdated(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        BenefitGrantUpdated::dispatch($billable, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Handle the benefit grant revoked event.
     *
     * @param  array<string, mixed>  $payload
     */
    private function handleBenefitGrantRevoked(array $payload): void
    {
        $billable = $this->resolveBillable($payload);

        BenefitGrantRevoked::dispatch($billable, $payload); // @phpstan-ignore-line argument.type - Billable is a instance of a model
    }

    /**
     * Resolve the billable from the payload.
     *
     * @param  array<string, mixed>  $payload
     * @return \ConfettiCode\LaravelPolar\Billable
     *
     * @throws InvalidMetadataPayload
     */
    private function resolveBillable(array $payload) // @phpstan-ignore-line return.trait - Billable is used in the user final code
    {
        $customerMetadata = $payload['customer']['metadata'] ?? null;

        if (!isset($customerMetadata) || !is_array($customerMetadata) || !isset($customerMetadata['billable_id'], $customerMetadata['billable_type'])) {
            throw new InvalidMetadataPayload();
        }

        return $this->findOrCreateCustomer(
            $customerMetadata['billable_id'],
            (string) $customerMetadata['billable_type'],
            (string) $payload['customer_id'],
        );
    }

    /**
     * Find or create a customer.
     *
     * @return \ConfettiCode\LaravelPolar\Billable
     */
    private function findOrCreateCustomer(int|string $billableId, string $billableType, string $customerId) // @phpstan-ignore-line return.trait - Billable is used in the user final code
    {
        return LaravelPolar::$customerModel::firstOrCreate([
            'billable_id' => $billableId,
            'billable_type' => $billableType,
        ], [
            'polar_id' => $customerId,
        ])->billable;
    }

    private function findSubscription(string $subscriptionId): ?Subscription
    {
        return LaravelPolar::$subscriptionModel::firstWhere('polar_id', $subscriptionId);
    }

    private function findOrder(string $orderId): ?Order
    {
        return LaravelPolar::$orderModel::firstWhere('polar_id', $orderId);
    }
}
