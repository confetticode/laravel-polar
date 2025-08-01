<?php

namespace ConfettiCode\LaravelPolar\Concerns;

use ConfettiCode\LaravelPolar\Enums\OrderStatus;
use ConfettiCode\LaravelPolar\LaravelPolar;
use ConfettiCode\LaravelPolar\Order;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ManagesOrders // @phpstan-ignore-line trait.unused - ManagesOrders is used in Billable trait
{
    /**
     * Get all of the orders for the billable.
     *
     * @return MorphMany<Order, covariant $this>
     */
    public function orders(): MorphMany
    {
        return $this->morphMany(LaravelPolar::$orderModel, 'billable')->orderByDesc('created_at');
    }

    /**
     * Determine if the billable has purchased a specific product.
     */
    public function hasPurchasedProduct(string $productId): bool
    {
        return $this->orders()->where('product_id', $productId)->where('status', OrderStatus::Paid)->exists();
    }
}
