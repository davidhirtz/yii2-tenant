<?php

declare(strict_types=1);

namespace Hirtz\Yii2\Tenant\Tests\Fixtures;

use davidhirtz\yii2\tenant\models\Tenant;
use yii\test\ActiveFixture;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;
}
