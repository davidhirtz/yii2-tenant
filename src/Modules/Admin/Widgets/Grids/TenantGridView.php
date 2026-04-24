<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Buttons\CreateButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DraggableSortGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\ViewGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\StatusIconColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\StatusFilterDropdown;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Hirtz\Tenant\Web\UrlManager;
use Iterator;
use Override;
use Stringable;
use Yii;

/**
 * @template T of TenantActiveDataProvider
 * @property T $provider
 */
class TenantGridView extends GridView
{
    #[Override]
    protected function configure(): void
    {
        $this->header ??= [
            $this->getStatusDropdown(),
            $this->getSearchInput(),
        ];

        $this->columns ??= [
            $this->getStatusColumn(),
            $this->getNameColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
        ];

        parent::configure();
    }

    protected function getStatusDropdown(): ?Stringable
    {
        return StatusFilterDropdown::make()
            ->model(Tenant::instance());
    }

    protected function getStatusColumn(): ?Column
    {
        return StatusIconColumn::make();
    }

    protected function getNameColumn(): ?Column
    {
        return DataColumn::make()
            ->property('name')
            ->content($this->getNameColumnContent(...));
    }

    protected function getNameColumnContent(Tenant $tenant): string
    {
        $name = $this->search->markKeywords($tenant->name);
        $url = $tenant->getAbsoluteUrl();

        return A::make()
                ->content($name)
                ->href($tenant->getAdminRoute())
                ->class('strong')
            . Div::make()
                ->class('small')
                ->content(
                    A::make()
                        ->content($url)
                        ->href($url)
                        ->target('_blank')
                );
    }

    protected function getUpdatedAtColumn(): ?Column
    {
        return RelativeTimeColumn::make()
            ->property('updated_at')
            ->hiddenForMediumDevices();
    }

    protected function getButtonColumn(): ?Column
    {
        return ButtonColumn::make()
            ->content($this->getButtonColumnContent(...));
    }

    protected function getButtonColumnContent(Tenant $tenant): Iterator
    {
        $manager = Yii::$app->getUrlManager();
        $tenantId = $manager instanceof UrlManager ? $manager->tenant?->id : null;

        if ($tenantId !== $tenant->id) {
            yield Button::make()
                ->secondary()
                ->icon('toggle-on')
                ->tooltip(Yii::t('tenant', 'TENANT_SWITCH_ADMIN_BUTTON'))
                ->url(Url::current(['tenant' => $tenant]));
        }

        if ($this->isSortable()) {
            yield DraggableSortGridButton::make();
        }

        if (Yii::$app->getUser()->can(Tenant::AUTH_TENANT_UPDATE, ['tenant' => $tenant])) {
            yield ViewGridButton::make()
                ->model($tenant);
        }
    }

    #[Override]
    protected function isSortable(): bool
    {
        return parent::isSortable() && null === $this->provider->status;
    }
}
