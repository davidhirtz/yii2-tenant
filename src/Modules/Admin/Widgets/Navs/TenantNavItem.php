<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Navs\NavItem;
use Hirtz\Tenant\Models\Tenant;
use Yii;

class TenantNavItem extends NavItem
{
    public function __construct(array $config = [])
    {
        $this->icon ??= 'network-wired';
        $this->label ??= Yii::t('tenant', 'TENANT_NAME_PLURAL');
        $this->order ??= 80;
        $this->roles ??= [Tenant::AUTH_TENANT_CREATE, Tenant::AUTH_TENANT_UPDATE];
        $this->url ??= ['/admin/tenant/tenant/index'];

        parent::__construct($config);
    }
}
