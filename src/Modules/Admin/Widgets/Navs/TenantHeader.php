<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Navs;

use Hirtz\Skeleton\Widgets\Buttons\CreateButton;
use Hirtz\Skeleton\Widgets\Navs\ModelHeader;
use Hirtz\Skeleton\Widgets\Traits\ProviderTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Override;
use Stringable;
use Yii;

/**
 * @extends ModelHeader<Tenant|null>
 */
class TenantHeader extends ModelHeader
{
    /**
     * @use ProviderTrait<TenantActiveDataProvider|null>
     */
    use ProviderTrait;

    #[Override]
    protected function configure(): void
    {
        if ($this->model) {
            $this->title ??= $this->model->getOldAttribute('name');
            $this->addContent($this->getTenantActionDropdown());
        }

        if ($this->provider) {
            $this->addContent($this->getCreateTenantButton());
        }

        $this->title ??= Yii::t('tenant', 'TENANT_NAME_PLURAL');

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
            ->label(Yii::t('tenant', 'TENANT_CREATE_BUTTON'))
            ->roles([Tenant::AUTH_TENANT]);
    }
}
