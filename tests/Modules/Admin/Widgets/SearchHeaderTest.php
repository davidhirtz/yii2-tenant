<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets;

use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\SearchHeader;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;
use Yii;
use yii\web\Controller;

class SearchHeaderTest extends TestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->controller = new Controller('search', Yii::$app);
    }

    #[Override]
    protected function tearDown(): void
    {
        TenantCollection::invalidateCache();
        parent::tearDown();
    }

    public function testTheSearchFiltersByTenant(): void
    {
        $this->getWebRequest()->setQueryParams(['q' => 'Å東京', 'tenant' => '2', 'page' => '3']);

        $html = SearchHeader::make()->query('Å東京')->render();

        self::assertStringContainsString('<a class="dropdown-option" href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=3">Draft Tenant</a>', $html);
        self::assertStringContainsString('<a class="selected dropdown-option" href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=2">German Tenant</a>', $html);
        self::assertStringContainsString('href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC"', $html);
    }

    public function testASingleTenantOffersNoFilter(): void
    {
        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        self::assertStringNotContainsString('dropdown', SearchHeader::make()->query('x')->render());
    }
}
