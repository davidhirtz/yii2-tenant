<?php

declare(strict_types=1);

namespace Hirtz\Tenant\modules\admin\widgets\grids;

use Hirtz\Skeleton\helpers\Html;
use Hirtz\Skeleton\html\A;
use Hirtz\Skeleton\html\Button;
use Hirtz\Skeleton\html\Div;
use Hirtz\Skeleton\widgets\grids\columns\ButtonColumn;
use Hirtz\Skeleton\widgets\grids\columns\buttons\DraggableSortGridButton;
use Hirtz\Skeleton\widgets\grids\columns\buttons\ViewGridButton;
use Hirtz\Skeleton\widgets\grids\columns\Column;
use Hirtz\Skeleton\widgets\grids\columns\DataColumn;
use Hirtz\Skeleton\widgets\grids\columns\RelativeTimeColumn;
use Hirtz\Skeleton\widgets\grids\GridView;
use Hirtz\Skeleton\widgets\grids\toolbars\CreateButton;
use Hirtz\Skeleton\widgets\grids\traits\StatusGridViewTrait;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\modules\admin\data\TenantActiveDataProvider;
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

    protected function isSortable(): bool
    {
        return parent::isSortable() && null === $this->provider->status;
    }
}
