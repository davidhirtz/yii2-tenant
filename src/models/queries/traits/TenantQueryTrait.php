<?php

declare(strict_types=1);

namespace Hirtz\Tenant\models\queries\traits;

use Hirtz\Tenant\models\Tenant;
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
