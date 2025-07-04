<?php

namespace ConfettiCode\LaravelPolar\Hooks;

use Illuminate\Support\Carbon;
use ConfettiCode\LaravelPolar\Order;
use ConfettiCode\LaravelPolar\LaravelPolar;
use ConfettiCode\LaravelPolar\Enums\OrderStatus;
use ConfettiCode\LaravelPolar\Handlers\AbstractHookHandler;

class OrderHandler extends AbstractHookHandler
{
    /**
     * @inheritdoc
     */
    public function handle(array $payload): void
    {
        if ($payload['type'] === 'order.created') {
            $this->handleCreated($payload['data']);
        } elseif ($payload['type'] === 'order.updated') {
            $this->handleUpdated($payload['data']);
        }
    }

    public function handleCreated(array $data): void
    {
        $billable = $this->resolveBillable($data);

        $billable->orders()->create([ // @phpstan-ignore-line class.notFound - the property is found in the billable model
            'polar_id' => $data['id'],
            'status' => $data['status'],
            'amount' => $data['amount'],
            'tax_amount' => $data['tax_amount'],
            'refunded_amount' => $data['refunded_amount'],
            'refunded_tax_amount' => $data['refunded_tax_amount'],
            'currency' => $data['currency'],
            'billing_reason' => $data['billing_reason'],
            'customer_id' => $data['customer_id'],
            'product_id' => $data['product_id'],
            'ordered_at' => Carbon::make($data['created_at']),
        ]);
    }

    public function handleUpdated(array $data): void
    {
        if (!($order = $this->findOrder($data['id'])) instanceof LaravelPolar::$orderModel) {
            return;
        }

        $status = $data['status'];
        $isRefunded = $status === OrderStatus::Refunded->value || $status === OrderStatus::PartiallyRefunded->value;

        $order->sync([
            ...$data,
            'status' => $status,
            'refunded_at' => $isRefunded ? Carbon::make($data['refunded_at']) : null,
            // Because the sync method requires $attributes to have "ordered_at" key.
            'ordered_at' => Carbon::make($data['created_at']),
        ]);
    }

    private function findOrder(string $orderId): ?Order
    {
        return LaravelPolar::$orderModel::firstWhere('polar_id', $orderId);
    }
}
