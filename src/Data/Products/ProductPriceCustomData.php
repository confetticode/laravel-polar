<?php

namespace ConfettiCode\LaravelPolar\Data\Products;

use Spatie\LaravelData\Attributes\MapName;

class ProductPriceCustomData extends ProductPriceData
{
    public function __construct(
        /**
         * Allowed value: `"custom"`
         */
        #[MapName('amount_type')]
        public readonly string $amountType,
        /**
         * The minimum amount the customer can pay.
         */
        #[MapName('minimum_amount')]
        public readonly ?int $minimumAmount,
        /**
         * The maximum amount the customer can pay.
         */
        #[MapName('maximum_amount')]
        public readonly ?int $maximumAmount,
        /**
         * The initial amount shown to the customer.
         */
        #[MapName('preset_amount')]
        public readonly ?int $presetAmount,
    ) {}
}
