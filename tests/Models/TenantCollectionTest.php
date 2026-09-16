<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Models;

use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Yii;

final class TenantCollectionTest extends TestCase
{
    use TenantFixtureTrait;

    public function testDefault(): void
    {
        $expected = $this->getTenantFixture()->data['default']['id'];
        self::assertEquals($expected, TenantCollection::getDefault()->id);
    }

    public function testVisibleTenants(): void
    {
        Yii::$app->set('tenant', TenantCollection::getDefault());

        $tenants = TenantCollection::getVisibleTenants();
        self::assertCount(2, $tenants);
    }

    /**
     * The records a request loaded must never reach the next one, so `Bootstrap` drops them — a reset that only
     * ran in the tests would leave a resident application serving them forever.
     */
    public function testTheTenantsDoNotOutliveTheApplication(): void
    {
        $tenant = TenantCollection::getDefault();
        self::assertNotNull($tenant);

        $this->reloadApplication();

        self::assertNotSame($tenant, TenantCollection::getDefault());
    }

    public function testGetById(): void
    {
        self::assertSame(2, TenantCollection::getById(2)?->id);
        self::assertNull(TenantCollection::getById(999));
    }

    /**
     * A new record carries no tenant id, and PHP 8.5 deprecates `null` as an array offset, which the web error
     * handler turns into an exception. The suite never sees one — the application's error handler replaces
     * PHPUnit's and answers `false` for whatever PHPUnit masked out of `error_reporting` — hence the handler here.
     */
    public function testGetByIdWithoutIdTriggersNoDeprecation(): void
    {
        $deprecations = [];

        set_error_handler(function (int $code, string $message) use (&$deprecations): bool {
            $deprecations[] = $message;
            return true;
        }, E_DEPRECATED);

        try {
            self::assertNull(TenantCollection::getById(null));
        } finally {
            restore_error_handler();
        }

        self::assertSame([], $deprecations);
    }

    public function testFromRequest(): void
    {
        Yii::$app->set('request', [
            'class' => Request::class,
            'hostInfo' => 'https://www.domain.localhost',
            'queryParams' => ['tenant' => 2],
        ]);

        $tenant = TenantCollection::getFromRequest();
        self::assertEquals(2, $tenant->id);
    }
}
