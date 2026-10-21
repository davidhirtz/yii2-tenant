<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Models;

use Hirtz\Skeleton\Models\Actions\RestoreTrail;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\ModuleTrait;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;

final class TenantRestoreTrailTest extends TestCase
{
    use ModuleTrait;
    use TenantFixtureTrait;

    public function testATenantIsNotRestoredWithoutTheAdminModule(): void
    {
        $tenant = $this->getTenantFromFixture();
        $tenant->name = 'Renamed';
        self::assertSame(1, $tenant->update(), implode(' ', $tenant->getErrorSummary(true)));

        $trail = $this->findLatestUpdate($tenant);
        self::assertTrue(RestoreTrail::isRestorable($trail));

        self::getModule()->enableAdminModule = false;

        self::assertFalse($tenant->getAdminRoute());
        self::assertFalse(RestoreTrail::isRestorable($trail));
    }

    private function findLatestUpdate(Tenant $tenant): Trail
    {
        $trail = Trail::find()
            ->where([
                'model_class' => $tenant->getTrailBehavior()->modelClass,
                'model_id' => (string)$tenant->id,
                'type' => Trail::TYPE_UPDATE,
            ])
            ->orderBy(['id' => SORT_DESC])
            ->one();

        self::assertInstanceOf(Trail::class, $trail);

        return $trail;
    }
}
