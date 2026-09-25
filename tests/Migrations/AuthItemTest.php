<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Migrations;

use Hirtz\Skeleton\Models\User;
use Hirtz\Tenant\Migrations\M260925110000ManagerTenantPermission;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\TestCase;
use Yii;

final class AuthItemTest extends TestCase
{
    /**
     * A tenant cuts the whole installation, so it is the administrator's alone — `manager` holds every other
     * permission.
     */
    public function testTheTenantPermissionIsTheAdministratorsAlone(): void
    {
        $auth = Yii::$app->getAuthManager();

        self::assertArrayHasKey(Tenant::AUTH_TENANT, $auth->getPermissionsByRole(User::AUTH_ROLE_ADMIN));
        self::assertArrayNotHasKey(Tenant::AUTH_TENANT, $auth->getPermissionsByRole(User::AUTH_ROLE_MANAGER));
    }

    public function testTheManagerTenantPermissionMigrationTakesTheTenantBack(): void
    {
        $auth = Yii::$app->getAuthManager();
        $manager = $auth->getRole(User::AUTH_ROLE_MANAGER) ?? self::fail('No manager role.');
        $tenant = $auth->getPermission(Tenant::AUTH_TENANT) ?? self::fail('No tenant permission.');

        $auth->addChild($manager, $tenant);
        self::assertArrayHasKey(Tenant::AUTH_TENANT, $auth->getPermissionsByRole(User::AUTH_ROLE_MANAGER));

        (new M260925110000ManagerTenantPermission())->safeUp();

        self::assertArrayNotHasKey(Tenant::AUTH_TENANT, $auth->getPermissionsByRole(User::AUTH_ROLE_MANAGER));
    }
}
