<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240819124324Tenant extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->createTable(Tenant::tableName(), [
            'id' => $this->primaryKey()->unsigned(),
            'status' => $this->tinyInteger()->notNull()->defaultValue(Tenant::STATUS_DEFAULT),
            'name' => $this->string()->notNull(),
            'url' => $this->string(100)->notNull()->unique(),
            'cookie_domain' => $this->string()->null(),
            'language' => $this->string(5)->notNull(),
            'position' => $this->integer()->unsigned()->notNull()->defaultValue(0),
            'updated_by_user_id' => $this->integer()->unsigned()->null(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->null(),
        ], $this->getTableOptions());

        $this->addForeignKey(
            'tenant_updated_by_user_id',
            Tenant::tableName(),
            'updated_by_user_id',
            User::tableName(),
            'id',
            'SET NULL'
        );

        TenantCollection::insertDefault();
    }

    public function safeDown(): void
    {
        $this->dropTable(Tenant::tableName());
    }
}
