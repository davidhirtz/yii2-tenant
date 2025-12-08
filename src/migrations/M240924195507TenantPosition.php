<?php

declare(strict_types=1);

namespace Hirtz\Tenant\migrations;

use Hirtz\Skeleton\db\traits\MigrationTrait;
use Hirtz\Tenant\models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240924195507TenantPosition extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        if ($this->getDb()->getTableSchema(Tenant::tableName())->getColumn('position')) {
            return;
        }

        $this->addColumn(Tenant::tableName(), 'position', (string)$this->integer()
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
