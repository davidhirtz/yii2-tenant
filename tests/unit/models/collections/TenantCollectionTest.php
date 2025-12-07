<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models\collections;

use davidhirtz\yii2\skeleton\test\TestCase;
use davidhirtz\yii2\skeleton\web\Application;
use davidhirtz\yii2\tenant\tests\fixtures\TenantFixture;
use Override;
use Yii;

final class TenantCollectionTest extends TestCase
{
    public function fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        $this->config = require(__DIR__ . '/../../../config.php');
        parent::setUp();
    }

    public function getTenantFixture(): TenantFixture
    {
        return $this->getFixture('tenant');
    }

    public function testDefault(): void
    {
        echo $this->getTenantFixture()->dataFile;
        self::assertTrue(Yii::$app instanceof Application);
    }
}
