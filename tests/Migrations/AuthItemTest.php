<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Migrations;

use Hirtz\Skeleton\Models\User;
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
}
