<?php

namespace davidhirtz\yii2\tenant\migrations;

use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\skeleton\db\traits\MigrationTrait;
use davidhirtz\yii2\skeleton\models\User;
use Yii;
use yii\db\Migration;

class M240905060625Roles extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        $auth = Yii::$app->getAuthManager();
        $admin = $auth->getRole(User::AUTH_ROLE_ADMIN);

        $tenantUpdate = $auth->createPermission(Tenant::AUTH_TENANT_UPDATE);
        $tenantUpdate->description = Yii::t('tenant', 'TENANT_AUTH_UPDATE', [], Yii::$app->sourceLanguage);
        $auth->add($tenantUpdate);

        $auth->addChild($admin, $tenantUpdate);

        $tenantCreate = $auth->createPermission(Tenant::AUTH_TENANT_CREATE);
        $tenantCreate->description = Yii::t('tenant', 'TENANT_AUTH_CREATE', [], Yii::$app->sourceLanguage);
        $auth->add($tenantCreate);

        $auth->addChild($admin, $tenantCreate);
        $auth->addChild($tenantUpdate, $tenantCreate);

        $tenantDelete = $auth->createPermission(Tenant::AUTH_TENANT_DELETE);
        $tenantDelete->description = Yii::t('tenant', 'TENANT_AUTH_DELETE', [], Yii::$app->sourceLanguage);
        $auth->add($tenantDelete);

        $auth->addChild($admin, $tenantDelete);
        $auth->addChild($tenantUpdate, $tenantDelete);
    }
    
    public function safeDown(): void
    {
        $auth = Yii::$app->getAuthManager();
        $this->delete($auth->itemTable, ['name' => Tenant::AUTH_TENANT_DELETE]);
        $this->delete($auth->itemTable, ['name' => Tenant::AUTH_TENANT_CREATE]);
        $this->delete($auth->itemTable, ['name' => Tenant::AUTH_TENANT_UPDATE]);
    }
}