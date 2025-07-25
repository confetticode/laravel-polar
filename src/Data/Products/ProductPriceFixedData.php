<?php

namespace ConfettiCode\LaravelPolar\Data\Products;

use Spatie\LaravelData\Attributes\MapName;

class ProductPriceFixedData extends ProductPriceData
{
    public function __construct(
        /**
         * Allowed value: `"fixed"`
         */
        #[MapName('amount_type')]
        public readonly string $amountType,
        /**
         * The currency.
         */
        #[MapName('price_currency')]
        public readonly string $priceCurrency,
        /**
         * The price in cents.
         */
        #[MapName('price_amount')]
        public readonly int $priceAmount,
    ) {}
}
