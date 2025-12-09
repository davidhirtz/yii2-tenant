<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Grids;

use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Html\A;
use Hirtz\Skeleton\Html\Button;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Widgets\Grids\Columns\ButtonColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\DraggableSortGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Buttons\ViewGridButton;
use Hirtz\Skeleton\Widgets\Grids\Columns\Column;
use Hirtz\Skeleton\Widgets\Grids\Columns\DataColumn;
use Hirtz\Skeleton\Widgets\Grids\Columns\RelativeTimeColumn;
use Hirtz\Skeleton\Widgets\Grids\GridView;
use Hirtz\Skeleton\Widgets\Grids\Toolbars\CreateButton;
use Hirtz\Skeleton\Widgets\Grids\Traits\StatusGridViewTrait;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Stringable;
use Yii;
use yii\helpers\Url;

/**
 * @template T of Tenant
 * @extends GridView<T>
 * @property TenantActiveDataProvider $provider
 */
class TenantGridView extends GridView
{
    use StatusGridViewTrait;

    #[\Override]
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

    protected function getButtonColumnContent(Tenant $tenant): array
    {
        $buttons = [];

        if (Yii::$app->get('tenant')->id !== $tenant->id) {
            $buttons[] = Button::make()
                ->secondary()
                ->icon('toggle-on')
                ->tooltip(Yii::t('tenant', 'TENANT_SWITCH_ADMIN_BUTTON'))
                ->href(Url::current(['tenant' => $tenant]));
        }

        if ($this->isSortable()) {
            $buttons[] = DraggableSortGridButton::make();
        }

        if (Yii::$app->getUser()->can(Tenant::AUTH_TENANT_UPDATE, ['tenant' => $tenant])) {
            $buttons[] = ViewGridButton::make();
        }

        return $buttons;
    }

    /**
     * @see TenantController::actionCreate()
     */
    protected function getCreateTenantButton(): ?Stringable
    {
        return Yii::$app->getUser()->can(Tenant::AUTH_TENANT_CREATE)
            ? CreateButton::make()->text(Yii::t('tenant', 'TENANT_CREATE_BUTTON'))
            : null;
    }

    #[\Override]
    protected function isSortable(): bool
    {
        return parent::isSortable() && null === $this->provider->status;
    }
}
