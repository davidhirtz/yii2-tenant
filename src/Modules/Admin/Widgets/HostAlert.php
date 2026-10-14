<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets;

use Hirtz\Skeleton\Web\Application;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;

class HostAlert extends \Hirtz\Skeleton\Modules\Admin\Widgets\HostAlert
{
    /**
     * An unknown host is answered by the default tenant, so its URL decides, not the one of this page's tenant.
     */
    #[Override]
    protected function isHostPinned(): bool
    {
        $app = Application::current();

        return $app->getRequest()->allowedHosts
            || $app->getUrlManager()->isHostInfoConfigured()
            || TenantCollection::getDefault()?->getHostInfo();
    }
}
