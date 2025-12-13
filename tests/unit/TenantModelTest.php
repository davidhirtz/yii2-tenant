<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Unit;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
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

        self::assertTrue($tenant->save());
        self::assertEquals('https://www.new-domain.localhost', $tenant->url);
        self::assertEquals(4, $tenant->position);
    }

    public function testLanguageValidation(): void
    {
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);
        self::assertCount(2, Tenant::getLanguages());

        $tenant = $this->createTenant();
        $tenant->url = 'https://www.test.de';
        $tenant->language = 'de';

        self::assertTrue($tenant->save());
        self::assertEquals('de', $tenant->language);

        $tenant->language = 'invalid-language';

        self::assertFalse($tenant->update());
        self::assertArrayHasKey('language', $tenant->getErrors());
    }

    public function testCookieDomainValidation(): void
    {
        $tenant = $this->createTenant();

        $tenant->url = 'https://www.test.com';
        $tenant->language = 'en-US';
        $tenant->cookie_domain = 'test.com';

        self::assertTrue($tenant->save());
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
        self::assertContains(Yii::t('tenant', 'TENANT_ERROR_DELETE_LAST'), $default->getErrors('id'));
    }

    private function createTenant(): Tenant
    {
        $tenant = Tenant::create();
        $tenant->status = Tenant::STATUS_ENABLED;
        $tenant->name = 'Test';

        return $tenant;
    }
}
