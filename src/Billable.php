<?php

namespace ConfettiCode\LaravelPolar;

use ConfettiCode\LaravelPolar\Concerns\ManagesCheckouts;
use ConfettiCode\LaravelPolar\Concerns\ManagesCustomer;
use ConfettiCode\LaravelPolar\Concerns\ManagesOrders;
use ConfettiCode\LaravelPolar\Concerns\ManagesSubscription;

trait Billable // @phpstan-ignore-line trait.unused - Billable is used in the user final code
{
    use ManagesCheckouts;
    use ManagesCustomer;
    use ManagesOrders;
    use ManagesSubscription;
}
