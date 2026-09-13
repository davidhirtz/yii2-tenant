<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\User;
use Yii;
use yii\db\Migration;

/**
 * The permission names and descriptions this creates are hardcoded: `M2609141[0-6]0000AuthItems` collapses them
 * into one permission per model, so neither the constants nor the message keys exist any more.
 *
 * @noinspection PhpUnused
 */

class M240905060625Roles extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $auth = Yii::$app->getAuthManager();
        $admin = $auth->getRole(User::AUTH_ROLE_ADMIN);

        $tenantUpdate = $auth->createPermission('tenantUpdate');
        $tenantUpdate->description = 'Update tenants';
        $auth->add($tenantUpdate);

        $auth->addChild($admin, $tenantUpdate);

        $tenantCreate = $auth->createPermission('tenantCreate');
        $tenantCreate->description = 'Create tenants';
        $auth->add($tenantCreate);

        $auth->addChild($admin, $tenantCreate);
        $auth->addChild($tenantUpdate, $tenantCreate);

        $tenantDelete = $auth->createPermission('tenantDelete');
        $tenantDelete->description = 'Delete tenants';
        $auth->add($tenantDelete);

        $auth->addChild($admin, $tenantDelete);
        $auth->addChild($tenantUpdate, $tenantDelete);
    }

    public function safeDown(): void
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => 'tenantDelete']);
        $this->delete($auth->itemTable, ['name' => 'tenantCreate']);
        $this->delete($auth->itemTable, ['name' => 'tenantUpdate']);
    }
}
