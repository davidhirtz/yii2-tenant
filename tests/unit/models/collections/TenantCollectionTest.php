<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\collections;

use davidhirtz\yii2\tenant\tests\base\TestCase;
use Override;
use Yii;

final class TenantCollectionTest extends TestCase
{
    #[Override]
    protected function setUp(): void
    {
        $this->config = require(__DIR__ . '/../../../config.php');
        parent::setUp();
    }

    public function testDefault(): void
    {
        dump(Yii::$app);
        self::assertTrue(true);
    }
}
