<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\support\traits;

use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\tests\fixtures\TenantFixture;
use davidhirtz\yii2\tenant\tests\support\UnitTester;

trait TenantFixtureTrait
{
    protected UnitTester $tester;

    public function _before(): void
    {
        TenantCollection::invalidateCache();
    }

    public function _fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
                'dataFile' => codecept_data_dir() . 'tenant.php',
            ],
        ];
    }
}
