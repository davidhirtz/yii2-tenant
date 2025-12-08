<?php

declare(strict_types=1);

namespace Hirtz\Tenant\test\fixtures;

use Hirtz\Tenant\models\Tenant;
use yii\test\ActiveFixture;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;
}
