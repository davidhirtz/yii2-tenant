<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Queries\Traits;

use Hirtz\Tenant\Models\Tenant;
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
