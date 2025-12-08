<?php

declare(strict_types=1);

namespace Hirtz\Tenant\modules\admin\controllers\traits;

use Hirtz\Tenant\models\Tenant;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

trait TenantControllerTrait
{
    private function findTenant(int $id, ?string $permission = null): Tenant
    {
        $tenant = Tenant::findOne($id);

        if (!$tenant) {
            throw new NotFoundHttpException();
        }

        if ($permission && !Yii::$app->getUser()->can($permission, ['tenant' => $tenant])) {
            throw new ForbiddenHttpException();
        }

        return $tenant;
    }
}
