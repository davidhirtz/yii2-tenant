<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\support\models;

use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\tenant\models\traits\TenantRelationTrait;

/**
 * @property int $id
 * @property null $tenant_id
 */
class TestModel extends ActiveRecord
{
    use TenantRelationTrait;

    /**
     * @return TestActiveQuery
     */
    public static function find(): ActiveQuery
    {
        return new TestActiveQuery(self::class);
    }

    public static function tableName(): string
    {
        return '{{%test}}';
    }
}