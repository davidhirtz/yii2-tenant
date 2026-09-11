<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260911120000CustomAttributes extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->addCustomAttributesColumn(Tenant::tableName());
    }

    public function safeDown(): void
    {
        $this->dropCustomAttributesColumn(Tenant::tableName());
    }
}
