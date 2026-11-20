<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Functional;

use Hirtz\Skeleton\Models\Forms\LoginForm;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Test\Traits\FunctionalTestTrait;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;

final class AdminTenantReloadTest extends TestCase
{
    use FunctionalTestTrait;
    use UserFixtureTrait;

    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'user' => UserFixture::class,
            'tenant' => TenantFixture::class,
        ];
    }

    public function testALinkIntoAnotherTenantLoadsThePage(): void
    {
        $this->login();
        $this->requestWithHtmx('https://www.domain.localhost/de/admin/user/index', 'https://www.domain.localhost/admin/user/index');

        self::assertResponseIsSuccessful();
        self::assertResponseHeaderSame('hx-redirect', 'https://www.domain.localhost/de/admin/user/index');
        self::assertResponseNotHasHeader('hx-refresh');
        self::assertSame('', self::$client->getResponse()->getContent());
    }

    public function testALinkWithinTheTenantSwapsAsUsual(): void
    {
        $this->login();
        $this->requestWithHtmx('https://www.domain.localhost/de/admin/user/index', 'https://www.domain.localhost/de/admin/dashboard/index?q=Å東京');

        self::assertResponseIsSuccessful();
        self::assertResponseNotHasHeader('hx-redirect');
        self::assertSelectorExists('#wrap');
    }

    private function requestWithHtmx(string $url, string $currentUrl): void
    {
        self::$crawler = self::$client->request('GET', $url, server: [
            'HTTP_HX_REQUEST' => 'true',
            'HTTP_HX_CURRENT_URL' => $currentUrl,
        ]);
    }

    private function login(): void
    {
        $user = $this->getUserFromFixture('owner');

        $this->open('https://www.domain.localhost/admin/account/login');

        $this->submit(values: $this->prefixFormValues(LoginForm::instance()->formName(), [
            'email' => $user->email,
            'password' => 'password',
        ]));
    }
}
