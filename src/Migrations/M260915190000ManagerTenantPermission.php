<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Hirtz\Tenant\Models\Tenant;
use yii\db\Migration;

/**
 * A tenant is installation-level configuration, like `system` — `Skeleton\Migrations\M260914190000ManagerRole`
 * handed `manager` every permission that existed, this one takes the tenant back out.
 *
 * @noinspection PhpUnused
 */
class M260915190000ManagerTenantPermission extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $auth = $this->getAuthManager();
        $manager = $auth->getRole(User::AUTH_ROLE_MANAGER);
        $tenant = $auth->getPermission(Tenant::AUTH_TENANT);

        if ($manager && $tenant) {
            $auth->removeChild($manager, $tenant);
            $auth->invalidateCache();
        }
    }

    public function safeDown(): void
    {
        $auth = $this->getAuthManager();
        $manager = $auth->getRole(User::AUTH_ROLE_MANAGER);
        $tenant = $auth->getPermission(Tenant::AUTH_TENANT);

        if ($manager && $tenant) {
            $auth->addChild($manager, $tenant);
            $auth->invalidateCache();
        }
    }
}
