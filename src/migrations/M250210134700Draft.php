<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\migrations;

use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use davidhirtz\yii2\tenant\models\Tenant;
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
