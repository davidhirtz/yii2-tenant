<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Widgets;

use Hirtz\Skeleton\Modules\Admin\Widgets\HostAlert;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;
use Yii;

class HostAlertTest extends TestCase
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

    public function testADefaultTenantWithAUrlPinsUnknownHosts(): void
    {
        $this->setTenantUrls('https://www.example.com');
        $this->parseRequest('https://www.example.net');

        self::assertSame('', HostAlert::make()->render());
    }

    public function testADefaultTenantWithoutAUrlIsWarnedAboutOnAPinnedTenantsHost(): void
    {
        $this->setTenantUrls(null);
        $this->parseRequest('https://www.example.org');

        self::assertFalse(Yii::$app->getUrlManager()->isHostInfoFromRequest());
        self::assertStringContainsString('allowedHosts', HostAlert::make()->render());
    }

    public function testAllowedHostsSilenceTheAlert(): void
    {
        $this->setTenantUrls(null);
        $this->parseRequest('https://www.example.org');
        $this->getWebRequest()->allowedHosts = ['www.example.org'];

        self::assertSame('', HostAlert::make()->render());
    }

    private function setTenantUrls(?string $defaultUrl): void
    {
        Tenant::updateAll(['url' => $defaultUrl], ['id' => 1]);
        Tenant::updateAll(['url' => 'https://www.example.org'], ['id' => 2]);

        TenantCollection::invalidateCache();
        self::assertSame(1, TenantCollection::getDefault()?->id);
    }

    private function parseRequest(string $hostInfo): void
    {
        $this->getWebRequest()->setHostInfo($hostInfo);
        Yii::$app->getUrlManager()->parseRequest($this->getWebRequest());
    }
}
