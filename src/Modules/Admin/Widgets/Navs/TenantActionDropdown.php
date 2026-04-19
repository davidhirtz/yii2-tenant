<?php
declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Widgets\Buttons\TenantDeleteButton;
use Override;
use Stringable;

class TenantActionDropdown extends ActionDropdown
{
    /**
     * @use ModelTrait<Tenant>
     */
    use ModelTrait;

    #[Override]
    protected function configure(): void
    {
        $this->addItem($this->getTenantDeleteButton());
        parent::configure();
    }

    protected function getTenantDeleteButton(): ?Stringable
    {
        return TenantDeleteButton::make()
            ->model($this->model);
    }
}