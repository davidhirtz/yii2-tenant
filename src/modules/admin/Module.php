<?php

namespace davidhirtz\yii2\tenant\modules\admin;

use davidhirtz\yii2\skeleton\modules\admin\ModuleInterface;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \davidhirtz\yii2\skeleton\modules\admin\Module $module
 */
class Module extends \davidhirtz\yii2\skeleton\base\Module implements ModuleInterface
{
    /**
     * @var array|string the navbar item url
     */
    public array|string $url = ['/admin/tenant/index'];

    public function init(): void
    {
        $this->controllerMap = ArrayHelper::merge($this->getCoreControllerMap(), $this->controllerMap);
        parent::init();
    }

    protected function getCoreControllerMap(): array
    {
        return [
            'tenant' => [
                'class' => TenantController::class,
                'viewPath' => '@tenant/modules/admin/views/tenant',
            ],
        ];
    }

    public function getDashboardPanels(): array
    {
        return [];
    }

    public function getName(): string
    {
        return Yii::t('tenant', 'TENANT_NAME_PLURAL');
    }

    public function getNavBarItems(): array
    {
        return [
            'tenants' => [
                'label' => $this->getName(),
                'icon' => 'network-wired',
                'roles' => [
                    Tenant::AUTH_TENANT_CREATE,
                    Tenant::AUTH_TENANT_UPDATE,
                ],
                'url' => ['/admin/tenant'],
                'active' => [
                    'admin/tenant/',
                ],
            ],
        ];
    }
}
