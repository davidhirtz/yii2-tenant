<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\modules\admin\widgets\grids;

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\html\A;
use davidhirtz\yii2\skeleton\html\Button;
use davidhirtz\yii2\skeleton\html\Div;
use davidhirtz\yii2\skeleton\widgets\grids\columns\ButtonColumn;
use davidhirtz\yii2\skeleton\widgets\grids\columns\buttons\DraggableSortGridButton;
use davidhirtz\yii2\skeleton\widgets\grids\columns\buttons\ViewGridButton;
use davidhirtz\yii2\skeleton\widgets\grids\columns\Column;
use davidhirtz\yii2\skeleton\widgets\grids\columns\DataColumn;
use davidhirtz\yii2\skeleton\widgets\grids\columns\RelativeTimeColumn;
use davidhirtz\yii2\skeleton\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\widgets\grids\toolbars\CreateButton;
use davidhirtz\yii2\skeleton\widgets\grids\traits\StatusGridViewTrait;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\data\TenantActiveDataProvider;
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
                        ->target('_blank'));
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
