<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use Override;
use yii\db\Migration;

/**
 * The name orders this before `Cms\Migrations\M260908100000Tenant`, which seeds the tenant every database must
 * have and leaves its URL empty when the installation names no canonical host.
 *
 * @noinspection PhpUnused
 */
class M260907110000NullableUrl extends Migration
{
    use MigrationTrait;

    #[Override]
    public function safeUp(): void
    {
        $this->alterColumn(Tenant::tableName(), 'url', (string)$this->string(100)->null());
    }

    /**
     * The unique index allows any number of NULLs but no second empty string, so the rows that have no URL are
     * numbered rather than emptied.
     */
    #[Override]
    public function safeDown(): void
    {
        $tenants = $this->getQuotedTableName(Tenant::tableName());
        $ids = $this->getDb()->createCommand("SELECT [[id]] FROM $tenants WHERE [[url]] IS NULL")->queryColumn();

        foreach ($ids as $id) {
            $this->update(Tenant::tableName(), ['url' => "https://tenant-$id.invalid"], ['id' => $id]);
        }

        $this->alterColumn(Tenant::tableName(), 'url', (string)$this->string(100)->notNull());
    }
}
