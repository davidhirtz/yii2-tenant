<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets;

use Hirtz\Skeleton\Models\Search;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Search\Search as SearchComponent;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

class SearchResultGridViewTest extends TestCase
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

    #[Override]
    protected function tearDown(): void
    {
        Search::deleteAll();
        parent::tearDown();
    }

    public function testTheTenantFilterIsInTheGridHeaderNotInThePageHeader(): void
    {
        $this->login();

        $html = $this->runIndex(['q' => 'Å東京', 'tenant' => '2', 'page' => '3']);

        $start = strpos($html, '<div class="grid-header"');
        self::assertNotFalse($start, 'No grid header.');

        $header = substr($html, $start, (strpos($html, '</table>', $start) ?: strlen($html)) - $start);

        self::assertStringContainsString('<a class="dropdown-option" href="/admin/search/index?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=3">Draft Tenant</a>', $header);
        self::assertStringContainsString('<a class="selected dropdown-option" href="/admin/search/index?q=%C3%85%E6%9D%B1%E4%BA%AC&amp;tenant=2">German Tenant</a>', $header);
        self::assertStringNotContainsString('&amp;tenant=', substr($html, 0, $start));
    }

    public function testASingleTenantOffersNoFilter(): void
    {
        $this->login();
        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        self::assertStringNotContainsString('tenant=', $this->runIndex(['q' => 'Å東京', 'model' => 'x']));
    }

    /**
     * The documents are committed, since InnoDB fulltext does not see uncommitted rows.
     */
    public function testAHitOfAnotherTenantShowsItInTheColumnNotAsABadge(): void
    {
        $html = $this->runIndexForTenantHit(2);

        self::assertMatchesRegularExpression('#<th[^>]*>Tenant</th>#', $html);
        self::assertMatchesRegularExpression('#<td[^>]*>German Tenant</td>#', $html);

        // Behind the icon, as in the other grids.
        self::assertMatchesRegularExpression('#<thead>.*?<th[^>]*>(<div>)?(</div>)?</th><th[^>]*>Tenant</th><th[^>]*><div>Result</div></th>#s', $html);
        self::assertStringNotContainsString('class="badge"', $html);
    }

    public function testHitsOfTheCurrentTenantShowNoColumn(): void
    {
        $html = $this->runIndexForTenantHit(1);

        self::assertStringContainsString('class="search-result" href=', $html);
        self::assertDoesNotMatchRegularExpression('#<th[^>]*>Tenant</th>#', $html);
    }

    private function runIndexForTenantHit(int $tenantId): string
    {
        $user = $this->login();
        Yii::$app->getDb()->getTransaction()?->commit();

        Yii::$app->set('search', ['class' => SearchComponent::class, 'models' => [SearchResultGridViewTestUser::class]]);
        SearchResultGridViewTestUser::$tenantId = $tenantId;

        $record = SearchResultGridViewTestUser::findOne($user->id) ?? self::fail('No user.');
        SearchComponent::getComponent()->index($record);

        return $this->runIndex(['q' => $user->name]);
    }

    /**
     * @param array<string, string> $params
     */
    private function runIndex(array $params): string
    {
        $manager = Yii::$app->getUrlManager();
        self::assertInstanceOf(UrlManager::class, $manager);
        $manager->setTenant(TenantCollection::getById(1) ?? self::fail('No default tenant.'));

        $this->getWebRequest()->setQueryParams($params);

        $html = Yii::$app->runAction('admin/search/index', $params);
        self::assertIsString($html);

        return $html;
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('admin');
        $this->assignPermission($user->id, User::AUTH_USER);
        $this->getWebUser()->setIdentity($user);

        return $user;
    }
}

/**
 * Users belong to no tenant, so the test gives the record one.
 */
class SearchResultGridViewTestUser extends User
{
    public static ?int $tenantId = null;

    #[Override]
    public function getSearchTenantId(): ?int
    {
        return static::$tenantId;
    }
}
