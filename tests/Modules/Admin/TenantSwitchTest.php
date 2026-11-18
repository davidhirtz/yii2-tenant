<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin;

use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Modules\Admin\Module as AdminModule;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Skeleton\Test\Traits\UserFixtureTrait;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Widgets\Buttons\TenantDropdownButton;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

final class TenantSwitchTest extends TestCase
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
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);
    }

    public function testTheNavbarSwitchesToEveryTenant(): void
    {
        $this->login(Tenant::AUTH_TENANT);

        $html = $this->getDropdownHtml($this->runIndex());

        self::assertStringContainsString('popovertarget="tenants">Default</button>', $html);
        self::assertStringContainsString('<a class="selected dropdown-option" href="/admin/tenant/tenant/index" hx-boost="false">Default</a>', $html);
        self::assertStringContainsString('<a class="dropdown-option" href="/de/admin/tenant/tenant/index" hx-boost="false">German Tenant</a>', $html);
        self::assertStringContainsString('<a class="dropdown-option" href="https://www.draft.localhost/admin/tenant/tenant/index" hx-boost="false">Draft Tenant</a>', $html);
    }

    public function testTheSwitchDropsTheGridFilterAndTheTenantParameter(): void
    {
        $this->login(Tenant::AUTH_TENANT);
        $this->getWebRequest()->setQueryParams(['q' => 'Default', 'tenant' => '2']);

        $html = $this->getDropdownHtml($this->runIndex());

        self::assertStringContainsString('href="https://www.draft.localhost/admin/tenant/tenant/index" hx-boost="false">Draft Tenant', $html);
        self::assertStringNotContainsString('q=', $html);
        self::assertStringNotContainsString('tenant=', $html);
    }

    public function testARecordPageSwitchesToTheDashboard(): void
    {
        $this->login(Tenant::AUTH_TENANT);

        $this->switchToDefault();
        $html = Yii::$app->runAction('admin/tenant/tenant/update', ['id' => 1]);
        self::assertIsString($html);

        self::assertStringContainsString('href="https://www.draft.localhost/admin/dashboard/index" hx-boost="false">Draft Tenant', $html);
    }

    public function testAnotherRoleIsAddedByABundle(): void
    {
        $this->login();
        self::assertSame('', $this->renderDropdown());

        TenantDropdownButton::addRoles(static fn (): array => [User::AUTH_ROLE_MANAGER]);
        $this->assignManagerRole($this->getUserFromFixture('admin')->id);

        self::assertStringContainsString('Draft Tenant', $this->renderDropdown());
    }

    public function testASingleTenantShowsNoSwitch(): void
    {
        Tenant::updateAll(['status' => Tenant::STATUS_DISABLED], ['>', 'id', 1]);
        TenantCollection::invalidateCache();
        $this->login(Tenant::AUTH_TENANT);

        self::assertSame('', $this->renderDropdown());
    }

    /**
     * The session keeps one content language per tenant, and a tenant without the stored one answers its own.
     */
    public function testAContentLanguageTheTargetLacksFallsBackToItsOwn(): void
    {
        $this->login(Tenant::AUTH_TENANT);

        Tenant::updateAll(['languages' => [], 'language' => 'en-US'], ['id' => 3]);
        TenantCollection::invalidateCache();

        $module = AdminModule::current();

        $this->switchToDefault();
        $module->setSessionContentLanguage('de');
        self::assertSame('de', $module->getContentLanguage());

        $this->getUrlManager()->setTenant(TenantCollection::getById(3) ?? self::fail('No draft tenant.'));
        self::assertSame('en-US', $module->getContentLanguage());

        $this->switchToDefault();
        self::assertSame('de', $module->getContentLanguage());
    }

    private function runIndex(): string
    {
        $this->switchToDefault();
        $html = Yii::$app->runAction('admin/tenant/tenant/index');
        self::assertIsString($html);

        return $html;
    }

    private function getDropdownHtml(string $html): string
    {
        preg_match('#<div class="dropdown"><button[^>]+popovertarget="tenants".*?</ul></div></div>#', $html, $matches);
        return $matches[0] ?? self::fail('No tenant switch in the navbar.');
    }

    private function renderDropdown(): string
    {
        $this->switchToDefault();
        return TenantDropdownButton::make()->render();
    }

    /**
     * `runAction()` parses no request, which sets the tenant.
     */
    private function switchToDefault(): void
    {
        $this->getUrlManager()->setTenant(TenantCollection::getById(1) ?? self::fail('No default tenant.'));
    }

    private function login(?string $permission = null): void
    {
        $user = $this->getUserFromFixture('admin');

        if ($permission) {
            $this->assignPermission($user->id, $permission);
        }

        $this->getWebUser()->setIdentity($user);
    }

    private function getUrlManager(): UrlManager
    {
        $manager = Yii::$app->getUrlManager();
        self::assertInstanceOf(UrlManager::class, $manager);

        return $manager;
    }
}
