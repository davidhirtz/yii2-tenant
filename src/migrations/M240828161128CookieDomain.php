<?php

namespace davidhirtz\yii2\tenant\migrations;

use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use davidhirtz\yii2\tenant\models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240828161128CookieDomain extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->addColumn(Tenant::tableName(), 'cookie_domain', $this->string()
            ->null()
            ->after('url'));
    }

    public function safeDown(): void
    {
        $this->dropColumn(Tenant::tableName(), 'cookie_domain');
    }
}