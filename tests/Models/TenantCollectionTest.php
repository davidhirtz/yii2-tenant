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
