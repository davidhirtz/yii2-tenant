<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M250210134700Draft extends Migration
{
    use MigrationTrait;

    public const STATUS_ENABLED = 1;

    public function safeUp(): void
    {
        $this->update(Tenant::tableName(), ['status' => Tenant::STATUS_ENABLED], ['status' => self::STATUS_ENABLED]);
    }

    public function safeDown(): void
    {
        $this->update(Tenant::tableName(), ['status' => Tenant::STATUS_ENABLED], ['status' => self::STATUS_ENABLED]);
    }
}
