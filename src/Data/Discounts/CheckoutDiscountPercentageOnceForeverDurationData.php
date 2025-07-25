<?php

namespace ConfettiCode\LaravelPolar\Data\Discounts;

use Spatie\LaravelData\Attributes\MapName;
use Spatie\LaravelData\Data;

class CheckoutDiscountPercentageOnceForeverDurationData extends Data
{
    public function __construct(
        /**
         * Available options: `once`, `forever`, `repeating`
         */
        public readonly string $duration,
        /**
         * Available options: `fixed`, `percentage`
         */
        public readonly string $type,
        #[MapName('basis_points')]
        public readonly int $basisPoints,
        /**
        * The ID of the object.
        */
        public readonly string $id,
        public readonly string $name,
        public readonly ?string $code,
    ) {}
}
