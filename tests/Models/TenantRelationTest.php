<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Models;

use Hirtz\Skeleton\Db\ActiveQuery;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Queries\Traits\TenantQueryTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Models\Traits\TenantRelationTrait;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Override;
use Yii;

final class TenantRelationTest extends TestCase
{
    use TenantFixtureTrait;

    #[Override]
    protected function setUpSchema(): void
    {
        Yii::$app->getDb()->createCommand()->createTable(TestModel::tableName(), [
            'id' => 'pk auto_increment',
            'tenant_id' => 'integer unsigned NOT NULL',
        ])->execute();

        Yii::$app->getDb()->createCommand()->addForeignKey(
            'fk_test_tenant_id',
            TestModel::tableName(),
            'tenant_id',
            Tenant::tableName(),
            'id',
            'CASCADE'
        )->execute();
    }

    #[Override]
    protected function tearDownSchema(): void
    {
        Yii::$app->getDb()->createCommand()->dropTable('test')->execute();
    }

    public function testRelationMethods(): TestModel
    {
        $tenant = TenantCollection::getDefault();

        $model = TestModel::create();
        $model->populateTenantRelation($tenant);

        self::assertTrue($model->save());

        self::assertEquals($tenant->id, $model->getTenant()->one()->getPrimaryKey());
        return $model;
    }

    public function testRelationAndQueryMethods(): void
    {
        $tenant = TenantCollection::getDefault();

        $model = TestModel::create();
        $model->tenant_id = $tenant->id;

        self::assertTrue($model->save());

        $actual = TestModel::find()->andWhereTenant($tenant)->one();

        self::assertEquals($model->id, $actual->id);

        Yii::$app->set('tenant', $tenant);

        $actual = TestModel::find()
            ->andWhereCurrentTenant()
            ->one();

        self::assertEquals($model->id, $actual->id);
    }
}

/**
 * @property int $id
 * @property int|null $tenant_id
 */
class TestModel extends ActiveRecord
{
    use TenantRelationTrait;

    /**
     * @return TestActiveQuery
     */
    #[Override]
    public static function find(): ActiveQuery
    {
        return new TestActiveQuery(self::class);
    }

    #[Override]
    public static function tableName(): string
    {
        return '{{%test}}';
    }
}

/**
 * @extends ActiveQuery<TestModel>
 */
class TestActiveQuery extends ActiveQuery
{
    use TenantQueryTrait;
}
