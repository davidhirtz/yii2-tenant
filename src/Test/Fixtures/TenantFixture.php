<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test\Fixtures;

use Hirtz\Tenant\Models\Tenant;
use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;
}
