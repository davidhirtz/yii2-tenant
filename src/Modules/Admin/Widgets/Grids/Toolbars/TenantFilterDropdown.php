<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids\Toolbars;

use Hirtz\Skeleton\Widgets\Grids\Toolbars\FilterDropdown;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;
use Yii;

class TenantFilterDropdown extends FilterDropdown
{
    protected string $paramName = 'tenant';

    #[Override]
    protected function configure(): void
    {
        $this->label ??= Yii::t('tenant', 'TENANT_SINGULAR');

        if (!$this->items) {
            foreach (TenantCollection::getAll() as $tenant) {
                $this->items[$tenant->id] = $tenant->name;
            }
        }

        parent::configure();
    }

    #[Override]
    public function isVisible(): bool
    {
        return parent::isVisible() && count(TenantCollection::getAll()) > 1;
    }
}
