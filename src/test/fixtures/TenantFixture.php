<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\test\fixtures;

use davidhirtz\yii2\tenant\models\Tenant;
use yii\test\ActiveFixture;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;
}
