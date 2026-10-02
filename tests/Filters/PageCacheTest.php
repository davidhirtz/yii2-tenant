<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Filters;

use Hirtz\Skeleton\Sitemap\Sitemap as BaseSitemap;
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

    public function testTheCacheVariesByTenant(): void
    {
        $first = $this->createFilterForTenant(1)->getCacheKey();
        $second = $this->createFilterForTenant(2)->getCacheKey();

        self::assertContains('tenant-1', $first);
        self::assertContains('tenant-2', $second);
    }

    public function testWithoutATenantTheKeyIsTheSkeletonsOwn(): void
    {
        $key = $this->createFilter()->getCacheKey();

        self::assertSame(Yii::$app->language, end($key));
    }

    /**
     * A project's callable replaces the variations, never the tenant.
     */
    public function testACallableVariationStillVariesByTenant(): void
    {
        $this->setUpTenant(2);

        $key = $this->createFilter(['variations' => fn (): array => ['own']])->getCacheKey();

        self::assertContains('own', $key);
        self::assertContains('tenant-2', $key);
    }

    public function testTheSitemapVariesByTenantWhateverTheProjectConfigures(): void
    {
        $this->setUpTenant(2);

        $sitemap = Yii::createObject([
            'class' => BaseSitemap::class,
            'variations' => fn (): array => [Yii::$app->language],
        ]);

        self::assertInstanceOf(BaseSitemap::class, $sitemap);
        self::assertSame([Yii::$app->language, 'tenant-2'], $sitemap->getVariations());
    }

    public function testTheSkeletonRulesStillApply(): void
    {
        $this->setUpTenant(1);

        self::assertTrue($this->createFilter()->enabled);

        $this->getWebRequest()->setIsDraft(true);
        self::assertFalse($this->createFilter()->enabled);
    }

    private function createFilterForTenant(int $id): TestPageCache
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

    /**
     * @param array<string, mixed> $config
     */
    private function createFilter(array $config = []): TestPageCache
    {
        return new TestPageCache($config);
    }
}

class TestPageCache extends PageCache
{
    /**
     * @return array<array-key, mixed>
     */
    public function getCacheKey(): array
    {
        return $this->calculateCacheKey();
    }
}
