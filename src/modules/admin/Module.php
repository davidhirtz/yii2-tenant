<?php

declare(strict_types=1);

namespace Hirtz\Tenant\modules\admin;

use Hirtz\Skeleton\Modules\Admin\Config\MainMenuItemConfig;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Override;
use Yii;
use yii\helpers\ArrayHelper;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public array|string $url = ['/admin/tenant/index'];

    #[Override]
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
                order: 80,
            ),
        ];
    }
}
