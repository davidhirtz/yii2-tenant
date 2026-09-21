<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\Interfaces\StatusAttributeInterface;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;
use yii\db\Expression;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260101000110TenantSeed extends Migration
{
    use MigrationTrait;

    #[Override]
    public function safeUp(): void
    {
        if (Tenant::find()->count() > 0) {
            return;
        }

        $this->insert(Tenant::tableName(), [
            'status' => StatusAttributeInterface::STATUS_ENABLED,
            'name' => Yii::$app->name,
            'url' => null,
            'language' => null,
            'position' => 1,
            'created_at' => new Expression('UTC_TIMESTAMP()'),
        ]);
    }

    #[Override]
    public function safeDown(): bool
    {
        echo "    > the seeded tenant is not removed, an installation without one cannot serve\n";
        return false;
    }
}
