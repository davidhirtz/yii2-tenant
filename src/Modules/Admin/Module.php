<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin;

use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantNavItem;
use Override;
use Yii;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'tenant';

    #[Override]
    public function dashboard(Dashboard $dashboard): Dashboard
    {
        return $dashboard;
    }

    public function getName(): string
    {
        return Yii::t('tenant', 'TENANT_NAME_PLURAL');
    }

    #[Override]
    public function aside(Nav $nav): Nav
    {
        return $nav->addItem(TenantNavItem::make());
    }
}
