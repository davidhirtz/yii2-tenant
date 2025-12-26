<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;

class TestCase extends \Hirtz\Skeleton\Test\TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $this->config ??= require(__DIR__ . '/../../config/test.php');
        parent::setUp();

        TenantCollection::invalidateCache();
    }

    protected function tearDown(): void
    {
        TenantCollection::invalidateCache();
        parent::tearDown();
    }
}
