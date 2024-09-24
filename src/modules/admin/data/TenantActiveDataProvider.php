<?php

namespace davidhirtz\yii2\tenant\modules\admin\data;

use davidhirtz\yii2\skeleton\data\ActiveDataProvider;
use davidhirtz\yii2\tenant\models\Tenant;
use yii\data\Pagination;
use yii\data\Sort;

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

        $this->query->orderBy(['position' => SORT_ASC]);
    }

    public function getPagination(): Pagination|false
    {
        return !$this->isOrderedByPosition() ? parent::getPagination() : false;
    }

    public function getSort(): Sort|false
    {
        return !$this->isOrderedByPosition() ? parent::getSort() : false;
    }

    public function setSort($value): void
    {
        if (is_array($value)) {
            $value['defaultOrder'] ??= ['position' => SORT_ASC];
        }

        parent::setSort($value);
    }

    public function isOrderedByPosition(): bool
    {
        return in_array(key($this->query->orderBy ?? []), [
            Tenant::tableName() . '.[[position]]',
            'position',
        ]);
    }
}
