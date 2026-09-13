<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\I18n\Message;
use Hirtz\Skeleton\Models\User;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260914140000AuthItems extends Migration
{
    use MigrationTrait;

    private const array LEGACY_TENANT = ['tenantCreate', 'tenantDelete', 'tenantUpdate'];

    public function safeUp(): void
    {
        $this->addPermission(Tenant::AUTH_TENANT, $this->getTenantDescription(), User::AUTH_ROLE_ADMIN);
        $this->replaceAuthItems(self::LEGACY_TENANT, Tenant::AUTH_TENANT);
    }

    public function safeDown(): void
    {
        $this->restoreAuthItems(self::LEGACY_TENANT, Tenant::AUTH_TENANT, $this->getTenantDescription());
    }

    private function getTenantDescription(): Message
    {
        return Message::make('tenant', 'AUTH_TENANT_DESCRIPTION');
    }
}
