<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\collections;

use davidhirtz\yii2\skeleton\tests\TestCase;
use davidhirtz\yii2\tenant\tests\base\TenantTestTrait;
use Yii;

final class TenantCollectionTest extends TestCase
{
    use TenantTestTrait;

    public function testDefault(): void
    {
        dump(Yii::$app);
        self::assertTrue(true);
    }
}
