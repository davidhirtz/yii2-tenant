<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260915170000CustomAttributesColumn extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->moveCustomAttributesColumn(Tenant::tableName(), 'language');
    }

    public function safeDown(): void
    {
        $this->moveCustomAttributesColumnToEnd(Tenant::tableName());
    }
}
