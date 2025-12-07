<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\Tests\Unit\Models\Collections;

use davidhirtz\yii2\skeleton\tests\TestCase;
use Override;
use Yii;

final class TenantCollectionTest extends TestCase
{

    public function testDefault(): void
    {
        dump(Yii::$app);
        self::assertTrue(true);
    }
}
