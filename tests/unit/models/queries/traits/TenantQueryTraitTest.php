<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\queries\traits;

use Codeception\Test\Unit;
use davidhirtz\yii2\tenant\tests\data\models\TestModel;
use davidhirtz\yii2\tenant\tests\data\traits\TenantRelationTrait;

class TenantQueryTraitTest extends Unit
{
    use TenantRelationTrait;

    public function testQueryMethods(): void
    {
        $tenant = $this->tester->grabTenant();
        $model = new TestModel(['tenant_id' => $tenant->id]);

        self::assertTrue($model->save());

        $actual = TestModel::find()
            ->andWhereTenant($tenant)
            ->one();

        self::assertEquals($model->id, $actual->id);
    }
}
