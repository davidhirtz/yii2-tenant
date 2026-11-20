<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids\Columns;

use Hirtz\Skeleton\Models\Interfaces\ModelReferenceInterface;
use Hirtz\Skeleton\Models\Interfaces\SearchableInterface;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;
use Yii;
use yii\base\Model;

/**
 * @template TModel of array|Model = Model
 * @extends DataColumn<TModel>
 */
class TenantColumn extends DataColumn
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->title ??= Yii::t('tenant', 'TENANT_SINGULAR');
        $this->value ??= fn (mixed $model): ?string => TenantCollection::getById($this->getTenantId($model))?->name;
        $this->enableSorting = false;

        parent::__construct($config);
    }

    /**
     * Appends the column when no column shows `$property`.
     *
     * @param array<Column|string|null> $columns
     * @return array<Column|string|null>
     */
    public static function insertAfter(array $columns, string $property, ?self $column = null): array
    {
        $column ??= static::make();
        $columns = array_values($columns);

        foreach ($columns as $position => $current) {
            if ($current instanceof DataColumn && $current->getProperty() === $property) {
                array_splice($columns, $position + 1, 0, [$column]);
                return $columns;
            }
        }

        return [...$columns, $column];
    }

    #[Override]
    public function isVisible(): bool
    {
        return parent::isVisible() && count(TenantCollection::getAll()) > 1;
    }

    protected function getTenantId(mixed $model): ?int
    {
        if ($model instanceof ModelReferenceInterface) {
            $model = $model->getModel();
        }

        return $model instanceof SearchableInterface ? $model->getSearchTenantId() : null;
    }
}
