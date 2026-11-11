<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Buttons\CreateButton;
use Hirtz\Skeleton\Widgets\Navs\ActionDropdown;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Widgets\Buttons\TenantDeleteButton;
use Override;
use Stringable;
use Yii;

class TenantActionDropdown extends ActionDropdown
{
    /**
     * @use ModelTrait<Tenant>
     */
    use ModelTrait;

    #[Override]
    protected function configure(): void
    {
        $this->addItem(
            $this->getCreateButton(),
            $this->getTenantDeleteButton(),
        );

        parent::configure();
    }

    /**
     * @see TenantController::actionCreate()
     */
    protected function getCreateButton(): ?Stringable
    {
        return CreateButton::make()
            ->primary()
            ->label(Yii::t('tenant', 'TENANT_CREATE_BUTTON'))
            ->roles([Tenant::AUTH_TENANT])
            ->url(['/admin/tenant/tenant/create']);
    }

    protected function getTenantDeleteButton(): ?Stringable
    {
        return TenantDeleteButton::make()
            ->model($this->model);
    }
}
