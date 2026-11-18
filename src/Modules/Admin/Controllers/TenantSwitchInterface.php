<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Controllers;

use Hirtz\Tenant\Modules\Admin\Module;
use yii\base\Action;

/**
 * @see Module::getSwitchUrl()
 */
interface TenantSwitchInterface
{
    /**
     * `null` leads to the dashboard.
     *
     * @return array<int|string, mixed>|null
     */
    public function getTenantSwitchRoute(Action $action): ?array;
}
