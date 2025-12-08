<?php

declare(strict_types=1);

namespace Hirtz\Tenant\modules\admin\data;

use Hirtz\Skeleton\data\ActiveDataProvider;
use Hirtz\Tenant\models\Tenant;
use Override;
use yii\data\Pagination;
use yii\data\Sort;

class TenantActiveDataProvider extends ActiveDataProvider
{
    public ?string $searchString = null;
    public ?int $status = null;

    public function __construct($config = [])
    {
        $this->query = Tenant::find();
        parent::__construct($config);
    }

    #[Override]
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

        if ($this->status !== null) {
            $this->query->andWhere(['status' => $this->status])
                ->orderBy(['name' => SORT_ASC]);
        } else {
            $this->query->orderBy(['position' => SORT_ASC]);
        }
    }

    #[Override]
    public function getPagination(): Pagination|false
    {
        return !$this->isOrderedByPosition() ? parent::getPagination() : false;
    }

    #[Override]
    public function getSort(): Sort|false
    {
        return !$this->isOrderedByPosition() ? parent::getSort() : false;
    }

    #[Override]
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
        ], true);
    }
}
