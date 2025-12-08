<?php

declare(strict_types=1);

namespace Hirtz\Tenant\models\traits;

use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Tenant\models\queries\TenantQuery;
use Hirtz\Tenant\models\Tenant;

/**
 * @property int|null $tenant_id
 * @property-read Tenant|null $tenant {@see static::getTenant()}
 * @mixin ActiveRecord
 */
trait TenantRelationTrait
{
    public function getTenant(): TenantQuery
    {
        /** @var TenantQuery $relation */
        $relation = $this->hasOne(Tenant::class, ['id' => 'tenant_id']);
        return $relation;
    }

    public function populateTenantRelation(?Tenant $tenant): void
    {
        $this->populateRelation('tenant', $tenant);
        $this->tenant_id = $tenant?->id;
    }
}
