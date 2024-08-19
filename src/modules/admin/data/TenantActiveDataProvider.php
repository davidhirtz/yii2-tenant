<?php

namespace davidhirtz\yii2\tenant\modules\admin\data;

use davidhirtz\yii2\skeleton\data\ActiveDataProvider;
use davidhirtz\yii2\tenant\models\Tenant;

class TenantActiveDataProvider extends ActiveDataProvider
{
    public ?string $searchString = null;

    public function __construct($config = [])
    {
        $this->query = Tenant::find();
        parent::__construct($config);
    }

    protected function prepareQuery(): void
    {
        $this->initQuery();
        parent::prepareQuery();
    }

    protected function initQuery(): void
    {
        if ($this->searchString !== null) {
            $this->query->andFilterWhere(['like', 'name', $this->searchString]);
        }
    }

    public function setSort($value): void
    {
        if (is_array($value)) {
            $value['defaultOrder'] ??= ['updated_at' => SORT_ASC];
        }

        parent::setSort($value);
    }
}
