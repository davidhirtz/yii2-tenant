<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Widgets\Grids\Columns\TenantColumn;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;

class TenantColumnTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'tenant' => TenantFixture::class,
        ];
    }

    #[Override]
    protected function tearDown(): void
    {
        TenantCollection::invalidateCache();
        parent::tearDown();
    }

    public function testTheColumnFollowsTheNamedOne(): void
    {
        $name = DataColumn::make()->property('name');
        $date = DataColumn::make()->property('updated_at');

        $columns = TenantColumn::insertAfter([null, $name, $date], 'name');

        self::assertCount(4, $columns);
        self::assertSame($name, $columns[1]);
        self::assertInstanceOf(TenantColumn::class, $columns[2]);
        self::assertSame($date, $columns[3]);

        self::assertInstanceOf(TenantColumn::class, TenantColumn::insertAfter([$name], 'missing')[1] ?? null);
    }

    public function testASingleTenantHidesTheColumn(): void
    {
        self::assertTrue(TenantColumn::make()->isVisible());

        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        self::assertFalse(TenantColumn::make()->isVisible());
    }
}
