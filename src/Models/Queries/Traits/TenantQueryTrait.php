<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Queries\Traits;

use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Web\UrlManager;
use Yii;

trait TenantQueryTrait
{
    public function andWhereCurrentTenant(): static
    {
        $manager = Yii::$app->getUrlManager();
        $tenant = $manager instanceof UrlManager ? $manager->tenant : null;

        return $tenant ? $this->andWhereTenant($tenant) : $this;
    }

    public function andWhereTenant(Tenant $tenant): static
    {
        return $this->andWhere([$this->getTableAlias() . '.[[tenant_id]]' => $tenant->id]);
    }
}
