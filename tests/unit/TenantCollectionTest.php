<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit;

use davidhirtz\yii2\skeleton\web\Request;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\test\TestCase;
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
