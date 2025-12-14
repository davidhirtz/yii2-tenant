<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240828161128CookieDomain extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        if (!$this->hasColumn(Tenant::tableName(), 'cookie_domain')) {
            $this->addColumn(Tenant::tableName(), 'cookie_domain', (string)$this->string()
                ->null()
                ->after('url'));
        }
    }

    public function safeDown(): void
    {
        $this->dropColumn(Tenant::tableName(), 'cookie_domain');
    }
}
