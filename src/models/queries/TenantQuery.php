<?php

namespace davidhirtz\yii2\tenant\models\queries;

use davidhirtz\yii2\skeleton\db\ActiveQuery;
use davidhirtz\yii2\tenant\models\Tenant;

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
