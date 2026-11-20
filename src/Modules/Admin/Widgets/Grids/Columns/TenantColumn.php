<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids\Columns;

use Closure;
use Hirtz\Skeleton\Models\Interfaces\ModelReferenceInterface;
use Hirtz\Skeleton\Models\Interfaces\SearchableInterface;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Modules\Admin\Module;
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
     * @var Closure(mixed): ?int|null
     */
    protected ?Closure $tenantId = null;

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
     * @param Closure(mixed): ?int $tenantId
     */
    public function tenantId(Closure $tenantId): static
    {
        $this->tenantId = $tenantId;
        return $this;
    }

    /**
     * Appends the column when no column shows `$property`.
     *
     * @param array<Column|string|null> $columns
     * @return array<Column|string|null>
     */
    public static function insertBefore(array $columns, string $property, ?self $column = null): array
    {
        $column ??= static::make();
        $columns = array_values($columns);

        foreach ($columns as $position => $current) {
            if ($current instanceof DataColumn && $current->getProperty() === $property) {
                array_splice($columns, $position, 0, [$column]);
                return $columns;
            }
        }

        return [...$columns, $column];
    }

    /**
     * Hidden while every row is the admin's tenant's.
     */
    #[Override]
    public function isVisible(): bool
    {
        if (!parent::isVisible() || count(TenantCollection::getAll()) < 2) {
            return false;
        }

        $current = Module::getCurrentTenant()?->id;

        foreach ($this->grid->getProvider()->getModels() as $model) {
            $tenantId = $this->getTenantId($model);

            if ($tenantId !== null && $tenantId !== $current) {
                return true;
            }
        }

        return false;
    }

    protected function getTenantId(mixed $model): ?int
    {
        if ($this->tenantId) {
            return ($this->tenantId)($model);
        }

        if ($model instanceof ModelReferenceInterface) {
            $model = $model->getModel();
        }

        return $model instanceof SearchableInterface ? $model->getSearchTenantId() : null;
    }
}
