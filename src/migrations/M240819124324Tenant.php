<?php

namespace davidhirtz\yii2\tenant\migrations;

use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;
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

        $this->insertDefaultTenant();
    }

    protected function insertDefaultTenant(): void
    {
        $tenant = Tenant::create();
        $tenant->loadDefaultValues();
        $tenant->name = 'Default';
        $tenant->url = 'https://www.example.com/';
        $tenant->language = Yii::$app->language;

        $tenant->insert(false);
    }

    public function safeDown(): void
    {
        $this->dropTable(Tenant::tableName());
    }
}
