<?php

namespace ConfettiCode\LaravelPolar\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CustomerUpdated
{
    use Dispatchable;
    use SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        /**
         * The billable entity.
         */
        public Model $billable,
        /**
         * The webhook payload.
         */
        public array $payload,
    ) {}
}
