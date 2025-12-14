<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test\Fixtures;

use Hirtz\Tenant\Models\Tenant;
use yii\test\ActiveFixture;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;
}
