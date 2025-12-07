<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\test;

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

    protected function getTenantFixture(string $key = 'default'): array
    {
        /** @var TenantFixture $fixture */
        $fixture = $this->getFixture('tenant');
        return $fixture->data[$key];
    }

}