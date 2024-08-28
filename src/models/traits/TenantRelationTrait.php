<?php

namespace davidhirtz\yii2\tenant\models\traits;

use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\models\queries\TenantQuery;

/**
 * @property int|null $tenant_id
 * @property-read Tenant|null $tenant {@see static::getTenant()}
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
