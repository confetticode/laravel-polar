<?php

namespace ConfettiCode\LaravelPolar\Data\Products;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class LegacyRecurringProductPriceData extends Data
{
    public function __construct(
        /**
         * Creation timestamp of the object.
         */
        #[MapName('created_at')]
        public readonly string $createdAt,
        /**
         * Last modification timestamp of the object.
         */
        #[MapName('modified_at')]
        public readonly ?string $modifiedAt,
        /**
         * The ID of the price.
         */
        public readonly string $id,
        /**
         * Whether the price is archived and no longer available.
         */
        #[MapName('is_archived')]
        public readonly bool $isArchived,
        /**
         * The ID of the product owning the price.
         */
        #[MapName('product_id')]
        public readonly string $productId,
        /**
         * The type of the price.
         *
         * Allowed value: `"recurring"`
         */
        public readonly string $type,
        /**
         * The recurring interval of the price.
         *
         * Available options: `month`, `year`
         */
        #[MapName('recurring_interval')]
        public readonly string $recurringInterval,
        public readonly bool $legacy,
    ) {}
}
