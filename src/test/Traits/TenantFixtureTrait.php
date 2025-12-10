<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test\Traits;

use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;

trait TenantFixtureTrait
{
    public function fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    protected function getTenantFromFixture(string $key): Tenant
    {
        /** @var TenantFixture $fixture */
        $fixture = $this->getFixture('tenant');
        return Tenant::findOne($fixture->data[$key]['id']);
    }
}
