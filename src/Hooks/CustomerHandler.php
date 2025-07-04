<?php

namespace ConfettiCode\LaravelPolar\Hooks;

use ConfettiCode\LaravelPolar\Handlers\AbstractHookHandler;

class CustomerHandler extends AbstractHookHandler
{
    /**
     * @inheritdoc
     */
    public function handle(array $payload)
    {
        if ($payload['type'] === 'customer.updated') {
            $this->handleUpdated($payload['data']);
        }
    }

    public function handleUpdated(array $data): void
    {
        $billable = $this->findOrCreateCustomer(
            $data['metadata']['billable_id'],
            $data['metadata']['billable_type'],
            $data['id'],
        );

        $billable->update([ // @phpstan-ignore-line class.notFound
            $billable->polarEmailField() => $data['email'], // @phpstan-ignore-line class.notFound
            $billable->polarNameField() => $data['name'], // @phpstan-ignore-line class.notFound
        ]);
    }
}
