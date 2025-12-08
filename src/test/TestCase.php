<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\test;

use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\test\fixtures\TenantFixture;
use Override;

class TestCase extends \davidhirtz\yii2\skeleton\test\TestCase
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
