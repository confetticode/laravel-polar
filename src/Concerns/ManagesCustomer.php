<?php

namespace ConfettiCode\LaravelPolar\Concerns;

use ConfettiCode\LaravelPolar\Customer;
use ConfettiCode\LaravelPolar\Data\Sessions\CustomerSessionCustomerIDCreateData;
use ConfettiCode\LaravelPolar\Exceptions\InvalidCustomer;
use ConfettiCode\LaravelPolar\Exceptions\PolarApiError;
use ConfettiCode\LaravelPolar\LaravelPolar;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Http\RedirectResponse;

trait ManagesCustomer // @phpstan-ignore-line trait.unused - ManagesCustomer is used in Billable trait
{
    /**
     * Create a customer record for the billable model.
     *
     * @param  array<string, string|int>  $attributes
     */
    public function createAsCustomer(array $attributes = []): Customer
    {
        return $this->customer()->create($attributes);
    }

    /**
     * Get the customer related to the billable model.
     *
     * @return MorphOne<Customer, covariant $this>
     */
    public function customer(): MorphOne
    {
        return $this->morphOne(LaravelPolar::$customerModel, 'billable');
    }

    /**
     * Define the billable field / property that represents name.
     */
    public function polarNameField(): string
    {
        return 'name';
    }

    /**
     * Get the billable's name to associate with Polar.
     */
    public function polarName(): ?string
    {
        return $this->{$this->polarNameField()} ?? null;
    }

    /**
     * Define the billable field / property that represents email.
     */
    public function polarEmailField(): string
    {
        return 'email';
    }

    /**
     * Get the billable's email address to associate with Polar.
     */
    public function polarEmail(): ?string
    {
        return $this->{$this->polarEmailField()} ?? null;
    }

    /**
     * Generate a redirect response to the billable's customer portal.
     */
    public function redirectToCustomerPortal(): RedirectResponse
    {
        return new RedirectResponse($this->customerPortalUrl());
    }

    /**
     * Get the customer portal url for this billable.
     *
     * @throws PolarApiError
     * @throws InvalidCustomer
     */
    public function customerPortalUrl(): string
    {
        if ($this->customer === null || $this->customer->polar_id === null) {
            throw InvalidCustomer::notYetCreated($this);
        }

        $response = LaravelPolar::createCustomerSession(new CustomerSessionCustomerIDCreateData(
            customerId: $this->customer->polar_id,
        ));

        if (!$response) {
            throw new PolarApiError('Failed to create customer session');
        }

        return $response->customerPortalUrl;
    }
}
