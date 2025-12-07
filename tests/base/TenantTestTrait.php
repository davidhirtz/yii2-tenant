<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\base;

use Override;

trait TenantTestTrait
{
    #[Override]
    protected function setUp(): void
    {
        $this->config = require(__DIR__ . '/../config.php');
        parent::setUp();
    }
}