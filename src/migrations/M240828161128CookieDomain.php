<?php

declare(strict_types=1);

namespace Hirtz\Tenant\migrations;

use Hirtz\Skeleton\db\traits\MigrationTrait;
use Hirtz\Tenant\models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */

class M240828161128CookieDomain extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $this->addColumn(Tenant::tableName(), 'cookie_domain', (string)$this->string()
            ->null()
            ->after('url'));
    }

    public function safeDown(): void
    {
        $this->dropColumn(Tenant::tableName(), 'cookie_domain');
    }
}
