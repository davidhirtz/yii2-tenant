<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Traits;

use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Tenant\Models\Queries\TenantQuery;
use Hirtz\Tenant\Models\Tenant;

/**
 * The foreign key is deliberately not declared here: a trait `@property` is flattened into the using class, so a
 * second declaration of the same name there silently drops that class's whole PHPDoc scope instead of being
 * reported (monorepo issue #125). Each using model declares the column with its own nullability.
 *
 * @property-read Tenant|null $tenant {@see static::getTenant()}
 * @mixin ActiveRecord
 */
trait TenantRelationTrait
{
    /**
     * @return TenantQuery<Tenant>
     */
    public function getTenant(): TenantQuery
    {
        /** @var TenantQuery<Tenant> $relation */
        $relation = $this->hasOne(Tenant::class, ['id' => 'tenant_id']);
        return $relation;
    }

    public function populateTenantRelation(?Tenant $tenant): void
    {
        $this->populateRelation('tenant', $tenant);
        $this->tenant_id = $tenant?->id;
    }
}
