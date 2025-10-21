<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\data\models;

use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\tenant\models\queries\traits\TenantQueryTrait;

/**
 * @extends ActiveQuery<TestModel>
 */
class TestActiveQuery extends ActiveQuery
{
    use TenantQueryTrait;
}
