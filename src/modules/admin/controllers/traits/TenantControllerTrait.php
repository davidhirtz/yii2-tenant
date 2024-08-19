<?php

namespace davidhirtz\yii2\tenant\modules\admin\controllers\traits;

use davidhirtz\yii2\tenant\models\Tenant;
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
