<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\Helpers\Html;
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
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Traits\StatusGridViewTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Hirtz\Tenant\Web\UrlManager;
use Iterator;
use Override;
use Stringable;
use Yii;

/**
 * @template T of Tenant
 * @extends GridView<T>
 * @property TenantActiveDataProvider $provider
 */
class TenantGridView extends GridView
{
    use StatusGridViewTrait;

    #[Override]
    protected function configure(): void
    {
        $this->model ??= Tenant::instance();

        $this->header ??= [
            $this->getStatusDropdown(),
            $this->search->getToolbarItem(),
        ];

        $this->columns ??= [
            $this->getStatusColumn(),
            $this->getNameColumn(),
            $this->getUpdatedAtColumn(),
            $this->getButtonColumn(),
        ];

        $this->footer ??= [
            $this->getCreateTenantButton(),
        ];

        parent::configure();
    }

    protected function getNameColumn(): ?Column
    {
        return DataColumn::make()
            ->property('name')
            ->content($this->getNameColumnContent(...));
    }

    protected function getNameColumnContent(Tenant $tenant): string
    {
        $name = Html::markKeywords(Html::encode($tenant->name), $this->search->getKeywords());
        $url = $tenant->getAbsoluteUrl();

        return A::make()
                ->content($name)
                ->href($this->getRoute($tenant))
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
                ->href(Url::current(['tenant' => $tenant]));
        }

        if ($this->isSortable()) {
            yield DraggableSortGridButton::make();
        }

        if (Yii::$app->getUser()->can(Tenant::AUTH_TENANT_UPDATE, ['tenant' => $tenant])) {
            yield ViewGridButton::make();
        }
    }

    /**
     * @see TenantController::actionCreate()
     */
    protected function getCreateTenantButton(): string|Stringable
    {
        return CreateButton::make()
            ->label(Yii::t('tenant', 'TENANT_CREATE_BUTTON'))
            ->roles([Tenant::AUTH_TENANT_CREATE]);
    }

    #[Override]
    protected function isSortable(): bool
    {
        return parent::isSortable() && null === $this->provider->status;
    }
}
