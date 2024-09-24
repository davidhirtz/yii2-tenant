<?php

namespace davidhirtz\yii2\tenant\migrations;

use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use davidhirtz\yii2\tenant\models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240924195507TenantPosition extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->addColumn(Tenant::tableName(), 'position', $this->integer()
            ->unsigned()
            ->notNull()
            ->defaultValue(0)
            ->after('language'));
    }

    public function safeDown(): void
    {
        $this->dropColumn(Tenant::tableName(), 'position');
    }
}