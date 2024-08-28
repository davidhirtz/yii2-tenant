<?php

namespace davidhirtz\yii2\tenant\models\queries\traits;

use davidhirtz\yii2\tenant\models\Tenant;
use Yii;

trait TenantQueryTrait
{
    public function andWhereCurrentTenant(): static
    {
        return $this->andWhereTenant(Yii::$app->get('tenant'));
    }

    public function andWhereTenant(Tenant $tenant): static
    {
        return $this->andWhere(['tenant_id' => $tenant->id]);
    }
}