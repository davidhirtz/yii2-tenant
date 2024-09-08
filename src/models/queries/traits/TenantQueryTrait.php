<?php

namespace davidhirtz\yii2\tenant\models\queries\traits;

use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;

/**
 * @mixin ActiveQuery
 */
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