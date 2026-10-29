<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Models;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Hirtz\Tenant\Web\UrlManager;
use Yii;

final class TenantModelTest extends TestCase
{
    use TenantFixtureTrait;

    public function testHostInfo(): void
    {
        self::assertEquals('https://www.domain.localhost', TenantCollection::getDefault()?->getHostInfo());
    }

    public function testPathInfo(): void
    {
        $tenant = $this->getTenantFromFixture('enabled');
        self::assertEquals('/de', $tenant->getPathInfo());
    }

    public function testUrlValidation(): void
    {
        $tenant = $this->createTenant();
        $tenant->url = 'www.domain.localhost';
        $tenant->language = 'en-US';

        self::assertFalse($tenant->save());
        self::assertArrayHasKey('url', $tenant->getErrors());

        $tenant->url = 'https://www.domain.localhost';

        self::assertFalse($tenant->save());

        $error = Yii::t('yii', '{attribute} "{value}" has already been taken.', [
            'attribute' => $tenant->getAttributeLabel('url'),
            'value' => $tenant->url,
        ]);

        self::assertContains($error, $tenant->getErrors('url'));

        $tenant->url = 'https://www.new-domain.localhost/';

        self::assertTrue($tenant->save(), print_r($tenant->getErrors(), true));
        self::assertEquals('https://www.new-domain.localhost', $tenant->url);
        self::assertEquals(4, $tenant->position);
    }

    /**
     * The column's collation treats them as one, so validation must too, or the insert fails on the unique key.
     */
    public function testAUrlApartOnlyInLetterCaseIsTaken(): void
    {
        $tenant = $this->createTenant();
        $tenant->url = 'https://WWW.Domain.localhost';
        $tenant->language = 'en-US';

        self::assertFalse($tenant->validate());
        self::assertArrayHasKey('url', $tenant->getErrors());
    }

    public function testLanguageValidation(): void
    {
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);

        // the select renders the values as they come, so the labels have to be the values of the map
        self::assertSame(['en-US' => 'English', 'de' => 'Deutsch'], Tenant::getLanguages());

        $tenant = $this->createTenant();
        $tenant->url = 'https://www.test.de';
        $tenant->language = 'de';

        self::assertTrue($tenant->save(), print_r($tenant->getErrors(), true));
        self::assertEquals('de', $tenant->language);

        $tenant->language = 'invalid-language';

        self::assertFalse($tenant->update());
        self::assertArrayHasKey('language', $tenant->getErrors());
    }

    public function testLanguageIsOptional(): void
    {
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);

        $tenant = $this->createTenant();
        $tenant->url = 'https://www.no-language.localhost';
        $tenant->language = '';

        self::assertTrue($tenant->validate(), print_r($tenant->getErrors(), true));
        self::assertNull($tenant->language);
    }

    public function testLanguagesKeepTheOthersBesideTheDefault(): void
    {
        $this->setLanguages('en-US');

        $tenant = $this->createTenant();
        $tenant->url = 'https://www.languages.localhost';
        $tenant->language = 'de';
        $tenant->load(['languages' => ['fr', 'invalid', 'de', ['de'], 'fr']], '');

        self::assertTrue($tenant->save(), print_r($tenant->getErrors(), true));
        self::assertSame(['fr'], $tenant->languages);
        self::assertSame(['de', 'fr'], $tenant->getAvailableLanguages());

        $tenant->refresh();
        self::assertSame(['fr'], $tenant->languages);
    }

    /**
     * Stored as `null`, a language the application adds later is offered too.
     */
    public function testEveryLanguageIsStoredAsNull(): void
    {
        $this->setLanguages('en-US');

        $tenant = $this->createTenant();
        $tenant->load(['languages' => ['de', 'fr', 'it']], '');

        self::assertTrue($tenant->validate(), print_r($tenant->getErrors(), true));
        self::assertNull($tenant->languages);
        self::assertSame(['en-US', 'de', 'fr', 'it'], $tenant->getAvailableLanguages());
    }

    public function testNoOtherLanguageIsASingleLanguageTenant(): void
    {
        $this->setLanguages('en-US');

        $tenant = $this->createTenant();
        $tenant->load(['languages' => ''], '');

        self::assertTrue($tenant->validate(), print_r($tenant->getErrors(), true));
        self::assertSame([], $tenant->languages);
        self::assertSame(['en-US'], $tenant->getAvailableLanguages());
    }

    public function testTheDefaultFollowsTheApplication(): void
    {
        $this->setLanguages('en-US');

        $tenant = $this->createTenant();
        $tenant->languages = ['fr'];

        self::assertSame('en-US', $tenant->getDefaultLanguage());
        self::assertSame(['en-US', 'fr'], $tenant->getAvailableLanguages());

        $this->setLanguages('de');

        self::assertSame('de', $tenant->getDefaultLanguage());
        self::assertSame(['de', 'fr'], $tenant->getAvailableLanguages());
    }

    public function testASingleLanguageTenantNeedsADefault(): void
    {
        $this->setLanguages(null);

        $tenant = $this->createTenant();
        $tenant->load(['languages' => ''], '');

        self::assertFalse($tenant->validate());
        self::assertSame([Yii::t('tenant', 'TENANT_LANGUAGES_DEFAULT_ERROR')], $tenant->getErrors('languages'));
    }

    public function testLanguagesNotPostedStayEveryLanguage(): void
    {
        $this->setLanguages('en-US');

        $tenant = $this->createTenant();
        $tenant->language = 'de';

        self::assertTrue($tenant->validate(), print_r($tenant->getErrors(), true));
        self::assertNull($tenant->languages);
    }

    public function testUrlIsOptional(): void
    {
        $tenant = $this->createTenant();
        $tenant->url = '';

        self::assertTrue($tenant->save(), print_r($tenant->getErrors(), true));
        self::assertNull($tenant->url);
        self::assertNull($tenant->getHostInfo());
        self::assertNull($tenant->getCookieDomain());
        self::assertSame('', $tenant->getPathInfo());
    }

    public function testASecondTenantWithoutAUrlIsNotATakenUrl(): void
    {
        $first = $this->createTenant();
        $first->url = null;

        $second = $this->createTenant();
        $second->url = null;

        self::assertTrue($first->save(), print_r($first->getErrors(), true));
        self::assertTrue($second->save(), print_r($second->getErrors(), true));
    }

    public function testCookieDomainValidation(): void
    {
        $tenant = $this->createTenant();

        $tenant->url = 'https://www.test.com';
        $tenant->language = 'en-US';
        $tenant->cookie_domain = 'test.com';

        self::assertTrue($tenant->save(), print_r($tenant->getErrors(), true));
        self::assertEquals('test.com', $tenant->cookie_domain);

        $tenant->cookie_domain = 'invalid-domain';

        self::assertFalse($tenant->update());
        self::assertArrayHasKey('cookie_domain', $tenant->getErrors());

        $tenant->cookie_domain = 'https://www.test.com';

        self::assertFalse($tenant->update());
        self::assertArrayHasKey('cookie_domain', $tenant->getErrors());
    }

    public function testDelete(): void
    {
        $tenant = $this->getTenantFromFixture('draft');

        self::assertEquals(1, $tenant->delete());
        self::assertTrue($tenant->isDeleted());
        self::assertCount(2, TenantCollection::getAll());

        $tenant = TenantCollection::getDefault();
        self::assertEquals(1, $tenant->delete());

        $default = TenantCollection::getDefault();
        $expected = $this->getTenantFromFixture('enabled');

        self::assertEquals($expected->id, $default->id);

        self::assertFalse($default->delete());
        self::assertContains(Yii::t('tenant', 'TENANT_DELETE_LAST_ERROR'), $default->getErrors('id'));
    }

    private function setLanguages(?string $defaultLanguage): void
    {
        Yii::$app->getI18n()->setLanguages(['en-US', 'de', 'fr', 'it']);

        Yii::$app->set('urlManager', [
            'class' => UrlManager::class,
            'defaultLanguage' => $defaultLanguage,
        ]);
    }

    private function createTenant(): Tenant
    {
        $tenant = Tenant::create();
        $tenant->status = Tenant::STATUS_ENABLED;
        $tenant->name = 'Test';

        return $tenant;
    }

    public function testTheLastEnabledTenantCanBeNeitherDisabledNorDeleted(): void
    {
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);

        $tenants = Tenant::find()->orderBy(['id' => SORT_ASC])->all();
        $last = array_pop($tenants);
        self::assertNotNull($last);

        foreach ($tenants as $tenant) {
            $tenant->status = Tenant::STATUS_DISABLED;
            self::assertNotFalse($tenant->update(), print_r($tenant->getErrors(), true));
        }

        $last->status = Tenant::STATUS_ENABLED;
        self::assertNotFalse($last->update());

        $last->status = Tenant::STATUS_DISABLED;
        self::assertFalse($last->validate());
        self::assertArrayHasKey('status', $last->getErrors());

        $last->refresh();
        self::assertFalse($last->isDeletable());
        self::assertFalse($last->delete());

        TenantCollection::invalidateCache();
        self::assertSame($last->id, TenantCollection::getDefault()?->id);

        // The status icon offers only a status the tenant can take.
        self::assertTrue($last->isStatusUpdatable());

        $last->status = Tenant::STATUS_DRAFT;
        self::assertNotFalse($last->update(), print_r($last->getErrors(), true));
        self::assertSame(Tenant::STATUS_DISABLED, $last->getNextStatus()?->value);
        self::assertFalse($last->isStatusUpdatable());
    }
}
