<?php

declare(strict_types=1);

namespace Hirtz\Yii2\Tenant\Tests\Unit\Models\Collections;

use Hirtz\Yii2\Tenant\Tests\TestCase;
use Yii;

final class TenantCollectionTest extends TestCase
{

    public function testDefault(): void
    {
        dump(Yii::$app);
        self::assertTrue(true);
    }
}
