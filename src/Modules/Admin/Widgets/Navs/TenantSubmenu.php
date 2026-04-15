<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Skeleton\Widgets\Navs\Submenu;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;

class TenantSubmenu extends Submenu
{
    /**
     * @use ModelTrait<Tenant>
     */
    use ModelTrait;

    #[Override]
    protected function configure(): void
    {
        $this->title ??= Yii::t('tenant', 'TENANT_NAME_PLURAL');
        $this->url ??= ['/admin/tenant/'];

        $this->view->addBreadcrumb(Yii::t('tenant', 'TENANT_NAME_PLURAL'), ['/admin/tenant/']);

        $this->items = [
            NavItem::make()
                ->label(Yii::t('tenant', 'TENANT_NAME_PLURAL'))
                ->icon('landmark')
                ->roles([Tenant::AUTH_TENANT_UPDATE])
                ->url(['/admin/tenant'])
                ->routes(['admin/tenant/'])
        ];

        parent::configure();
    }
}
