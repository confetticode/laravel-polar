<?php

namespace ConfettiCode\LaravelPolar\Hooks;

use Illuminate\Support\Carbon;
use ConfettiCode\LaravelPolar\LaravelPolar;
use ConfettiCode\LaravelPolar\Subscription;
use ConfettiCode\LaravelPolar\Handlers\AbstractHookHandler;

class SubscriptionHandler extends AbstractHookHandler
{
    /**
     * @inheritdoc
     */
    public function handle(array $payload): void
    {
        if ($payload['type'] === 'subscription.created') {
            $this->handleCreated($payload['data']);

            return;
        } elseif (in_array($payload['type'], [
            'subscription.updated',
            'subscription.active',
            'subscription.canceled',
            'subscription.revoked',
        ])) {
            $data = $payload['data'];

            if (!($subscription = $this->findSubscription($data['id'])) instanceof LaravelPolar::$subscriptionModel) {
                return;
            }

            $subscription->sync($data);
        }
    }

    public function handleCreated(array $data): void
    {
        $billable = $this->resolveBillable($data);

        $productMetadata = $data['product']['metadata'];

        $billable->subscriptions()->create([ // @phpstan-ignore-line class.notFound - the property is found in the billable model
            'type' => $productMetadata['subscription_type'] ?? 'default',
            'polar_id' => $data['id'],
            'status' => $data['status'],
            'product_id' => $data['product_id'],
            'current_period_end' => $data['current_period_end'] ? Carbon::make($data['current_period_end']) : null,
            'ends_at' => $data['ends_at'] ? Carbon::make($data['ends_at']) : null,
        ]);

        if ($billable->customer->polar_id === null) { // @phpstan-ignore-line property.notFound - the property is found in the billable model
            $billable->customer->update(['polar_id' => $data['customer_id']]); // @phpstan-ignore-line property.notFound - the property is found in the billable model
        }
    }

    private function findSubscription(string $subscriptionId): ?Subscription
    {
        return LaravelPolar::$subscriptionModel::firstWhere('polar_id', $subscriptionId);
    }
}
