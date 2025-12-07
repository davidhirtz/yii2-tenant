<?php

declare(strict_types=1);

namespace Hirtz\Yii2\Tenant\Tests;

abstract class TestCase extends \davidhirtz\yii2\skeleton\tests\TestCase
{
    protected function setUp(): void
    {
        $this->config = require __DIR__ . '/../config.php';
        parent::setUp();
    }
}
