<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Migrations;

use Hirtz\Tenant\Migrations\M260101000110TenantSeed;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\TestCase;

final class TenantSeedTest extends TestCase
{
    /**
     * A tenant's statuses are draft statuses: `StatusAttributeInterface::STATUS_ENABLED` is a draft here, which
     * sends `X-Robots-Tag: none` from every page.
     */
    public function testTheSeededTenantIsEnabled(): void
    {
        Tenant::deleteAll();

        (new M260101000110TenantSeed(['compact' => true]))->safeUp();

        $tenant = Tenant::find()->one();

        self::assertNotNull($tenant);
        self::assertTrue($tenant->isEnabled());
        self::assertFalse($tenant->isDraft());
    }
}
