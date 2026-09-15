<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test\Traits;

use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use RuntimeException;

trait TenantFixtureTrait
{
    /**
     * @return array<string, mixed>
     */
    public function fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    protected function getTenantFixture(): TenantFixture
    {
        /** @var TenantFixture $fixture */
        $fixture = $this->getFixture('tenant');
        return $fixture;
    }

    protected function getTenantFromFixture(string $key = 'default'): Tenant
    {
        $fixture = $this->getTenantFixture();
        return Tenant::findOne($fixture->data[$key]['id'])
            ?? throw new RuntimeException("Tenant fixture \"$key\" was not loaded.");
    }
}
