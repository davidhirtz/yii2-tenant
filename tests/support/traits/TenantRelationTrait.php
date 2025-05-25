<?php

namespace davidhirtz\yii2\tenant\tests\support\traits;

use Yii;

trait TenantRelationTrait
{
    use TenantFixtureTrait;

    public function _before(): void
    {
        Yii::$app->getDb()->createCommand()->createTable('test', [
            'id' => 'pk auto_increment',
            'tenant_id' => 'integer unsigned NOT NULL',
        ])->execute();

        Yii::$app->getDb()->createCommand()->addForeignKey(
            'fk_test_tenant_id',
            'test',
            'tenant_id',
            'tenant',
            'id',
            'CASCADE'
        )->execute();

        $this->invalidateCache();
    }

    protected function _after(): void
    {
        Yii::$app->getDb()->createCommand()->dropTable('test')->execute();
    }

}