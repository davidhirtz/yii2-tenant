<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Queries;

use Hirtz\Skeleton\Db\ActiveQuery;
use Hirtz\Tenant\Models\Tenant;

/**
 * @template T of Tenant
 * @extends ActiveQuery<T>
 */
class TenantQuery extends ActiveQuery
{
    public function matching(?string $search): static
    {
        if ($search = $this->sanitizeSearchString($search)) {
            $this->andFilterWhere(['like', 'name', $search]);
        }

        return $this;
    }
}
