<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin;

use Hirtz\Skeleton\Test\TestCase;
use Hirtz\Skeleton\Widgets\Navs\Breadcrumbs;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

class ActiveTenantTest extends TestCase
{
    #[Override]
    protected function tearDown(): void
    {
        parent::tearDown();
        TenantCollection::invalidateCache();
    }

    public function testTheDefaultTenantAddsNoBreadcrumb(): void
    {
        $default = $this->createTenant('Erste', 'https://www.example.com/erste', 0);
        $this->switchTo($default);

        $html = Breadcrumbs::make()->alwaysShowHomeLink()->render();

        self::assertStringNotContainsString('Erste', $html);
    }

    public function testAnotherTenantFollowsTheSiteNameAndLinksItsHomepage(): void
    {
        $this->createTenant('Erste', 'https://www.example.com/erste', 0);
        $tenant = $this->createTenant('Zweite Å 東京', 'https://www.example.com/zweite', 1);
        $this->switchTo($tenant);

        $html = Breadcrumbs::make()->alwaysShowHomeLink()->render();

        self::assertStringContainsString(
            '<a class="breadcrumbs-link" href="' . $tenant->getAbsoluteUrl() . '" hx-boost="false">Zweite Å 東京</a>',
            $html,
        );

        self::assertLessThan(strpos($html, 'Zweite'), strpos($html, Yii::$app->name));
    }

    public function testTheHomepageShortcutOpensTheActiveTenant(): void
    {
        $this->createTenant('Erste', 'https://www.example.com/erste', 0);
        $tenant = $this->createTenant('Zweite', 'https://www.example.com/zweite', 1);
        $this->switchTo($tenant);

        $dashboard = Dashboard::make();
        $html = $dashboard->render();

        self::assertNotNull($dashboard->getItem('homepage'));
        self::assertStringContainsString('href="' . $tenant->getAbsoluteUrl() . '"', $html);
    }

    /**
     * The default tenant is the first by position, so every other tenant is moved behind the ones created here.
     */
    private function createTenant(string $name, string $url, int $position): Tenant
    {
        Tenant::updateAllCounters(['position' => 100], ['>=', 'position', $position]);

        $tenant = Tenant::create();
        $tenant->status = Tenant::STATUS_ENABLED;
        $tenant->name = $name;
        $tenant->url = $url;

        self::assertTrue($tenant->insert(), print_r($tenant->getErrors(), true));

        $tenant->updateAttributes(['position' => $position]);
        TenantCollection::invalidateCache();

        return $tenant;
    }

    private function switchTo(Tenant $tenant): void
    {
        $manager = Yii::$app->getUrlManager();
        self::assertInstanceOf(UrlManager::class, $manager);

        $manager->setTenant($tenant);
    }
}
