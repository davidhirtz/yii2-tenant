<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

class Module extends \Hirtz\Skeleton\Base\Module
{
    /**
     * @var bool whether the tenant admin module should be registered. With it off the routes 404 rather than a
     * hidden nav item covering a live controller, and the tenant can only be changed via console or SQL.
     */
    public bool $enableAdminModule = true;
}
