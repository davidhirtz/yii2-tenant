<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Filters;

use Hirtz\Tenant\Filters\PageCache;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

/**
 * Every tenant answers on its own host, so a cached page that did not vary by tenant would be served to the wrong
 * one.
 */
class PageCacheTest extends TestCase
{
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

    public function testTheCacheVariesByTenant(): void
    {
        $first = $this->createFilterForTenant(1);
        $second = $this->createFilterForTenant(2);

        self::assertContains(1, $first->variations);
        self::assertContains(2, $second->variations);

        self::assertNotSame($first->variations, $second->variations);
    }

    public function testWithoutATenantTheVariationsAreTheSkeletonsOwn(): void
    {
        $filter = $this->createFilter();

        self::assertNotEmpty($filter->variations);
        self::assertSame(Yii::$app->language, end($filter->variations));
    }

    /**
     * A callable decides for itself, so nothing is appended to it.
     */
    public function testACallableVariationIsLeftAlone(): void
    {
        $this->setUpTenant(1);

        $callback = fn (): array => ['own'];
        $filter = $this->createFilter(['variations' => $callback]);

        self::assertSame($callback, $filter->variations);
    }

    public function testTheSkeletonRulesStillApply(): void
    {
        $this->setUpTenant(1);

        self::assertTrue($this->createFilter()->enabled);

        Yii::$app->getRequest()->setIsDraft(true);
        self::assertFalse($this->createFilter()->enabled);
    }

    private function createFilterForTenant(int $id): PageCache
    {
        $this->setUpTenant($id);
        return $this->createFilter();
    }

    private function setUpTenant(int $id): void
    {
        $manager = Yii::$app->getUrlManager();

        self::assertInstanceOf(UrlManager::class, $manager);
        $manager->tenant = Tenant::findOne($id);
    }

    private function createFilter(array $config = []): PageCache
    {
        return new PageCache($config);
    }
}
