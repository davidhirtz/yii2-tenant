<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Hirtz\Tenant\Models\Tenant;
use Override;
use yii\db\Migration;

/**
 * A project whose v2 history had no tenant bundle was upgraded without the migration that takes the tenant back
 * from `manager`, so the role kept it.
 *
 * @noinspection PhpUnused
 */
class M260925110000ManagerTenantPermission extends Migration
{
    use MigrationTrait;

    #[Override]
    public function safeUp(): void
    {
        $auth = $this->getAuthManager();
        $manager = $auth->getRole(User::AUTH_ROLE_MANAGER);
        $tenant = $auth->getPermission(Tenant::AUTH_TENANT);

        if ($manager && $tenant && $auth->hasChild($manager, $tenant)) {
            $auth->removeChild($manager, $tenant);
            $auth->invalidateCache();
        }
    }

    /**
     * Nothing records whether the role held it, and it should not have.
     */
    #[Override]
    public function safeDown(): void
    {
    }
}
