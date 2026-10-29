<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * Off, the admin routes 404 rather than a hidden nav item covering a live controller.
     */
    public bool $enableAdminModule = true;
}
