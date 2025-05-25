<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\unit\models;

use Codeception\Test\Unit;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\tests\support\traits\TenantFixtureTrait;
use davidhirtz\yii2\tenant\tests\support\UnitTester;
use Yii;

class TenantTest extends Unit
{
    protected UnitTester $tester;
    use TenantFixtureTrait;

    public function testUrl(): void
    {
        $tenant = Tenant::create();
        $tenant->status = Tenant::STATUS_ENABLED;
        $tenant->name = 'Test';
        $tenant->url = 'www.domain.com';
        $tenant->language = 'en-US';

        self::assertFalse($tenant->save());
        self::assertArrayHasKey('url', $tenant->getErrors());

        $tenant->url = 'https://www.domain.com';

        self::assertFalse($tenant->save());

        $error = Yii::t('yii', '{attribute} "{value}" has already been taken.', [
            'attribute' => $tenant->getAttributeLabel('url'),
            'value' => $tenant->url,
        ]);

        self::assertContains($error, $tenant->getErrors('url'));

        $tenant->url = 'https://www.new-domain.com/';

        self::assertTrue($tenant->save());
        self::assertEquals('https://www.new-domain.com', $tenant->url);
    }
}
