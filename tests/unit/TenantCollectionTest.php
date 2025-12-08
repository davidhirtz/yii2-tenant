<?php

declare(strict_types=1);

namespace Hirtz\Tenant\tests\unit;

use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\models\collections\TenantCollection;
use Hirtz\Tenant\test\TestCase;
use Yii;

final class TenantCollectionTest extends TestCase
{
    public function testDefault(): void
    {
        $tenant = $this->getTenantFromFixture();
        self::assertEquals($tenant->id, TenantCollection::getDefault()->id);
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
            'hostInfo' => 'https://www.domain.com',
            'queryParams' => ['tenant' => 2],
        ]);

        $tenant = TenantCollection::getFromRequest();
        self::assertEquals(2, $tenant->id);
    }
}
