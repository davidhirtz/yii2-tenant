<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Widgets\Grids\Columns\TenantColumn;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;
use yii\data\ArrayDataProvider;

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

    public function testTheColumnPrecedesTheNamedOne(): void
    {
        $name = DataColumn::make()->property('name');
        $date = DataColumn::make()->property('updated_at');

        $columns = TenantColumn::insertBefore([null, $name, $date], 'name');

        self::assertCount(4, $columns);
        self::assertInstanceOf(TenantColumn::class, $columns[1]);
        self::assertSame($name, $columns[2]);
        self::assertSame($date, $columns[3]);

        self::assertInstanceOf(TenantColumn::class, TenantColumn::insertBefore([$name], 'missing')[1] ?? null);
    }

    public function testTheColumnShowsOnlyForARowOfAnotherTenant(): void
    {
        $this->setCurrentTenant(1);

        self::assertFalse($this->createColumn([1, null, 1])->isVisible());
        self::assertTrue($this->createColumn([1, 2])->isVisible());
    }

    public function testASingleTenantHidesTheColumn(): void
    {
        $this->setCurrentTenant(1);

        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        self::assertFalse($this->createColumn([1, 2])->isVisible());
    }

    /**
     * @param list<int|null> $tenantIds
     */
    private function createColumn(array $tenantIds): TenantColumn
    {
        $rows = array_map(fn (?int $id): array => ['tenant_id' => $id], $tenantIds);

        $grid = GridView::make()
            ->provider(new ArrayDataProvider(['allModels' => $rows]));

        $column = TenantColumn::make()
            ->tenantId(fn (array $row): ?int => $row['tenant_id']);

        $column->grid($grid);

        return $column;
    }

    private function setCurrentTenant(int $id): void
    {
        $manager = Yii::$app->getUrlManager();
        self::assertInstanceOf(UrlManager::class, $manager);

        $manager->setTenant(TenantCollection::getById($id) ?? self::fail('No tenant.'));
    }
}
