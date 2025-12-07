<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\collections;

use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\test\TestCase;
use davidhirtz\yii2\tenant\tests\fixtures\TenantFixture;
use Override;
use Yii;
use yii\test\Fixture;

final class TenantCollectionTest extends TestCase
{
    public function testDefault(): void
    {
        self::assertEquals(1, TenantCollection::getDefault()->id);
    }
}
