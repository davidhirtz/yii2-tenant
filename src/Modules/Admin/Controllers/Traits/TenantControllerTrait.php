<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Controllers\Traits;

use Hirtz\Tenant\Models\Tenant;
use yii\web\NotFoundHttpException;

trait TenantControllerTrait
{
    private function findTenant(int $id): Tenant
    {
        $tenant = Tenant::findOne($id);

        if (!$tenant) {
            throw new NotFoundHttpException();
        }

        return $tenant;
    }
}
