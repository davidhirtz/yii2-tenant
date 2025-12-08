<?php

declare(strict_types=1);

namespace Hirtz\Tenant\tests\unit;

use Hirtz\Skeleton\db\ActiveQuery;
use Hirtz\Skeleton\db\ActiveRecord;
use Hirtz\Tenant\models\collections\TenantCollection;
use Hirtz\Tenant\models\queries\traits\TenantQueryTrait;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\models\traits\TenantRelationTrait;
use Hirtz\Tenant\test\TestCase;
use Override;
use Yii;

final class TenantRelationTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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

    protected function tearDown(): void
    {
        Yii::$app->getDb()->createCommand()->dropTable('test')->execute();
        parent::tearDown();
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
     * @return TestActiveQuery<TestModel>
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
 * @template T of TestModel
 * @extends ActiveQuery<T>
 */
class TestActiveQuery extends ActiveQuery
{
    use TenantQueryTrait;
}
