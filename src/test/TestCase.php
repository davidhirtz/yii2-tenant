<?php

declare(strict_types=1);

namespace Hirtz\Tenant\test;

use Hirtz\Tenant\models\collections\TenantCollection;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\test\fixtures\TenantFixture;
use Override;

class TestCase extends \Hirtz\Skeleton\test\TestCase
{
    public function fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        $this->config = require(__DIR__ . '/../../tests/config.php');
        parent::setUp();
    }

    protected function tearDown(): void
    {
        TenantCollection::invalidateCache();
        parent::tearDown();
    }

    protected function getTenantFromFixture(string $key = 'default'): Tenant
    {
        /** @var TenantFixture $fixture */
        $fixture = $this->getFixture('tenant');
        return Tenant::findOne($fixture->data[$key]['id']);
    }
}
