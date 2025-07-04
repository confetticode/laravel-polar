<?php

namespace ConfettiCode\LaravelPolar\Handlers;

use ConfettiCode\LaravelPolar\LaravelPolar;
use ConfettiCode\LaravelPolar\Exceptions\InvalidMetadataPayload;
use ConfettiCode\LaravelPolar\Hooks\HookHandler;

abstract class AbstractHookHandler implements HookHandler
{
    /**
     * Find or create a customer.
     *
     * @return \ConfettiCode\LaravelPolar\Billable
     */
    protected function findOrCreateCustomer(int|string $billableId, string $billableType, string $customerId) // @phpstan-ignore-line return.trait - Billable is used in the user final code
    {
        return LaravelPolar::$customerModel::firstOrCreate([
            'billable_id' => $billableId,
            'billable_type' => $billableType,
        ], [
            'polar_id' => $customerId,
        ])->billable;
    }

    /**
     * Resolve the billable from the data.
     *
     * @param  array<string, mixed>  $data
     * @return \ConfettiCode\LaravelPolar\Billable
     *
     * @throws InvalidMetadataPayload
     */
    protected function resolveBillable(array $data) // @phpstan-ignore-line return.trait - Billable is used in the user final code
    {
        $customerMetadata = $data['customer']['metadata'] ?? null;

        if (!isset($customerMetadata) || !is_array($customerMetadata) || !isset($customerMetadata['billable_id'], $customerMetadata['billable_type'])) {
            throw new InvalidMetadataPayload();
        }

        return $this->findOrCreateCustomer(
            $customerMetadata['billable_id'],
            (string) $customerMetadata['billable_type'],
            (string) $data['customer_id'],
        );
    }
}
