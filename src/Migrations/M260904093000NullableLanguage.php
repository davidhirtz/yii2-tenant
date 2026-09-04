<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M260904093000NullableLanguage extends Migration
{
    use MigrationTrait;

    #[Override]
    public function safeUp(): void
    {
        $this->alterColumn(Tenant::tableName(), 'language', (string)$this->string(5)->null());
    }

    #[Override]
    public function safeDown(): void
    {
        $this->alterColumn(Tenant::tableName(), 'language', (string)$this->string(5)->notNull());
    }
}
