<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\traits;

use Codeception\Test\Unit;
use davidhirtz\yii2\tenant\tests\support\models\TestModel;
use davidhirtz\yii2\tenant\tests\support\traits\TenantRelationTrait;

class TenantRelationTraitTest extends Unit
{
    use TenantRelationTrait;

    public function testRelationMethods(): void
    {
        $tenant = $this->tester->grabTenant();

        $model = TestModel::create();
        $model->populateTenantRelation($tenant);

        self::assertTrue($model->save());

        self::assertEquals($tenant->id, $model->getTenant()->one()->id);
    }
}
