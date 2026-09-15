<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Functional;

use Hirtz\Skeleton\Models\Forms\LoginForm;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Test\Traits\FunctionalTestTrait;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Web\UrlManager;
use Hirtz\Tenant\Test\TestCase;
use Override;
use Yii;

/**
 * An administrator does not have to speak the language of the tenant they are editing, so the tenant's language
 * stays on the frontend — `Web\UrlManager::setLanguage()` sets it as the default language of the request.
 */
final class AdminLanguageTest extends TestCase
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
            'user' => [
                'class' => UserFixture::class,
            ],
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        $config = require(__DIR__ . '/../../config/test.php');
        $config['components']['i18n']['languages'] = ['en-US', 'de'];
        $config['components']['urlManager']['i18nUrl'] = true;

        $this->config = $config;

        parent::setUp();
    }

    public function testTheTenantLanguageStaysOutOfTheAdmin(): void
    {
        $this->login();

        // the German tenant of the fixture lives under the path `/de`
        $this->open('https://www.domain.localhost/de/admin/user/index');

        $urlManager = Yii::$app->getUrlManager();

        self::assertResponseIsSuccessful();
        self::assertInstanceOf(UrlManager::class, $urlManager);
        self::assertSame('de', $urlManager->tenant?->language);
        self::assertSame('en', self::$crawler->filter('html')->attr('lang'));
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('owner');

        $this->open('https://www.domain.localhost/admin/account/login');

        $this->submit(values: $this->prefixFormValues(LoginForm::instance()->formName(), [
            'email' => $user->email,
            'password' => 'password',
        ]));

        return $user;
    }
}
