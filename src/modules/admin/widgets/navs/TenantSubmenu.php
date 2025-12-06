<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\modules\admin\widgets\navs;

use davidhirtz\yii2\skeleton\widgets\navs\NavItem;
use davidhirtz\yii2\skeleton\widgets\navs\Submenu;
use davidhirtz\yii2\skeleton\widgets\traits\ModelWidgetTrait;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;

/**
 * @template T of Tenant
 * @property T $tenant
 */
class TenantSubmenu extends Submenu
{
    use ModelWidgetTrait;

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
