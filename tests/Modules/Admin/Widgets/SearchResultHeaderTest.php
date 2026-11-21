<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets;

use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Modules\Admin\Widgets\Grids\Toolbars\SearchResultHeader;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;
use Yii;
use yii\web\Controller;

class SearchResultHeaderTest extends TestCase
{
    use UserFixtureTrait;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'tenant' => TenantFixture::class,
            'user' => UserFixture::class,
        ];
    }

    public function testTheSearchFiltersByTenant(): void
    {
        Yii::$app->controller = new Controller('search', Yii::$app);
        $this->getWebRequest()->setQueryParams(['q' => 'Å東京', 'tenant' => '2', 'page' => '3']);

        $html = SearchResultHeader::make()->render();

        self::assertStringContainsString('<a class="dropdown-option" href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=3">Draft Tenant</a>', $html);
        self::assertStringContainsString('<a class="selected dropdown-option" href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=2">German Tenant</a>', $html);
        self::assertStringContainsString('href="/search?q=%C3%85%E6%9D%B1%E4%BA%AC"', $html);
    }

    public function testASingleTenantOffersNoFilter(): void
    {
        Yii::$app->controller = new Controller('search', Yii::$app);
        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        self::assertSame('', SearchResultHeader::make()->render());
    }

    public function testTheTenantFilterIsInTheGridHeaderNotInThePageHeader(): void
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignPermission($user->id, User::AUTH_USER);
        $this->getWebUser()->setIdentity($user);

        $this->getWebRequest()->setQueryParams(['q' => 'Å東京']);

        $html = Yii::$app->runAction('admin/search/index', ['q' => 'Å東京']);
        self::assertIsString($html);

        $start = strpos($html, '<div class="grid-header">');
        self::assertNotFalse($start, 'No grid header.');

        $end = strpos($html, 'id="search-results-list"', $start);
        self::assertNotFalse($end);

        self::assertStringContainsString('q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=2">German Tenant</a>', substr($html, $start, $end - $start));
        self::assertStringNotContainsString('&amp;tenant=', substr($html, 0, $start));
    }
}
