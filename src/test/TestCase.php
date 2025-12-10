<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;

class TestCase extends \Hirtz\Skeleton\Test\TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $this->config = require(__DIR__ . '/../../tests/config.php');
        parent::setUp();
    }

    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        TenantCollection::insertDefault();
    }
}
