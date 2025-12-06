<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\modules\admin;

use davidhirtz\yii2\skeleton\modules\admin\config\MainMenuItemConfig;
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
    public array|string $url = ['/admin/tenant/index'];

    #[\Override]
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

    public function getMainMenuItems(): array
    {
        return [
            'tenants' => new MainMenuItemConfig(
                label: $this->getName(),
                url: $this->url,
                icon: 'network-wired',
                roles: [
                   Tenant::AUTH_TENANT_CREATE,
                   Tenant::AUTH_TENANT_UPDATE,
                ],
                routes: [
                    'admin/tenant/',
                ],
            ),
        ];
    }

    #[\Override]
    public function beforeAction($action): bool
    {
        $this->setViewPath('@tenant/modules/admin/views');
        return parent::beforeAction($action);
    }
}
