<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\data\models;

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
    #[\Override]
    public static function find(): ActiveQuery
    {
        return new TestActiveQuery(self::class);
    }

    #[\Override]
    public static function tableName(): string
    {
        return '{{%test}}';
    }
}
