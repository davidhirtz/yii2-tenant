<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\collections;

use Codeception\Test\Unit;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\tests\data\traits\RequestTrait;
use davidhirtz\yii2\tenant\tests\data\traits\TenantFixtureTrait;
use Yii;

class TenantCollectionTest extends Unit
{
    use RequestTrait;
    use TenantFixtureTrait;

    public function testDefault(): void
    {
        $tenant = $this->tester->grabTenant();
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
        $this->getRequest([
            'hostInfo' => 'https://www.domain.com',
            'queryParams' => ['tenant' => 2],
        ]);

        $tenant = TenantCollection::getFromRequest();
        self::assertEquals(2, $tenant->id);
    }
}
