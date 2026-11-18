<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Web;

use Hirtz\Skeleton\Test\Browser;
use Hirtz\Skeleton\Test\Traits\FunctionalTestTrait;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Yii;

final class LoginHandoffTest extends TestCase
{
    use FunctionalTestTrait;
    use TenantFixtureTrait;

    public function testTheTenantHostsAreHandedTheDefaultTenantsLogin(): void
    {
        self::assertSame(['https://www.domain.localhost', 'https://www.draft.localhost'], TenantCollection::getHandoffHostInfos('https://www.domain.localhost'));

        $this->request('https://www.draft.localhost/admin/account/login');

        self::assertResponseStatusCodeSame(302);
        self::assertStringStartsWith(
            'https://www.domain.localhost/admin/sso?return=' . rawurlencode('https://www.draft.localhost/admin/'),
            $this->getLocation(),
        );
    }

    /**
     * The fixture's German tenant gives the auth host the cookie domain `.domain.localhost`, which a subdomain naming it
     * shares: its guest is the auth host's guest, so nothing is handed to it (#801).
     */
    public function testAHostSharingTheAuthHostsCookieDomainIsHandedNothing(): void
    {
        $tenant = Tenant::create();
        $tenant->loadDefaultValues();
        $tenant->name = 'Shop';
        $tenant->language = 'en-US';
        $tenant->url = 'https://shop.domain.localhost';
        $tenant->cookie_domain = '.domain.localhost';
        self::assertTrue($tenant->insert(), implode(' ', $tenant->getErrorSummary(true)));
        TenantCollection::invalidateCache();

        self::assertNotNull(TenantCollection::getByUrl('https://shop.domain.localhost'));
        self::assertSame(['https://www.domain.localhost', 'https://www.draft.localhost'], TenantCollection::getHandoffHostInfos('https://www.domain.localhost'));

        $this->request('https://shop.domain.localhost/admin/account/login');
        self::assertSelectorExists('input[name="Login[password]"]');
    }

    public function testAPathTenantOnTheAuthHostShowsTheLoginForm(): void
    {
        $this->request('https://www.domain.localhost/de/admin/account/login');
        self::assertSelectorExists('input[name="Login[password]"]');
    }

    public function testTheAuthHostParamWins(): void
    {
        Yii::$app->params['authHostInfo'] = 'https://www.draft.localhost';

        $this->request('https://www.domain.localhost/admin/account/login');

        self::assertStringStartsWith('https://www.draft.localhost/admin/sso?', $this->getLocation());
    }

    public function testNothingChangesWithASingleTenant(): void
    {
        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();

        $this->request('https://www.draft.localhost/admin/account/login');
        self::assertSelectorExists('input[name="Login[password]"]');
    }

    private function request(string $uri): void
    {
        self::$client = new Browser(['HTTPS' => 'on']);
        self::$client->followRedirects(false);
        self::$crawler = self::$client->request('GET', $uri);
    }

    private function getLocation(): string
    {
        $location = self::$client->getResponse()->getHeader('location');
        self::assertIsString($location);

        return $location;
    }
}
