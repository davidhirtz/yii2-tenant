<?php

declare(strict_types=1);

namespace Hirtz\Tenant\models\queries;

use Hirtz\Skeleton\db\ActiveQuery;
use Hirtz\Tenant\models\Tenant;

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
