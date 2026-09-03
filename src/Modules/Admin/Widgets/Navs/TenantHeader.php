<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Widgets\Buttons\CreateButton;
use Hirtz\Skeleton\Widgets\Navs\Header;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Override;
use Stringable;
use Yii;

class TenantHeader extends Header
{
    /**
     * @use ModelTrait<Tenant|null>
     */
    use ModelTrait;

    /**
     * @use ProviderTrait<TenantActiveDataProvider|null>
     */
    use ProviderTrait;

    #[Override]
    protected function configure(): void
    {
        $this->title ??= $this->model?->getOldAttribute('name') ?? Lang::t('tenant', 'TENANT_NAME_PLURAL');

        if ($this->model) {
            $this->addContent($this->getTenantActionDropdown());
        }

        if ($this->provider) {
            $this->addContent($this->getCreateTenantButton());
        }

        if (!$this->provider) {
            $this->view->addBreadcrumb(Lang::t('tenant', 'TENANT_NAME_PLURAL'), ['/admin/tenant/']);
        }

        parent::configure();
    }

    protected function getTenantActionDropdown(): ?Stringable
    {
        return TenantActionDropdown::make()
            ->model($this->model);
    }

    /**
     * @see TenantController::actionCreate()
     */
    protected function getCreateTenantButton(): string|Stringable
    {
        return CreateButton::make()
            ->label(Lang::t('tenant', 'TENANT_CREATE_BUTTON'))
            ->roles([Tenant::AUTH_TENANT_CREATE]);
    }
}
