<?php

namespace davidhirtz\yii2\tenant\modules\admin\widgets\grids;

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\GridView;
use davidhirtz\yii2\skeleton\modules\admin\widgets\grids\traits\StatusGridViewTrait;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Icon;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\data\TenantActiveDataProvider;
use davidhirtz\yii2\timeago\TimeagoColumn;
use Yii;
use yii\helpers\Url;

/**
 * @property TenantActiveDataProvider $dataProvider
 */
class TenantGridView extends GridView
{
    use StatusGridViewTrait;

    public function init(): void
    {
        if (!$this->columns) {
            $this->columns = [
                $this->statusColumn(),
                $this->nameColumn(),
                $this->updatedAtColumn(),
                $this->buttonsColumn(),
            ];
        }

        parent::init();
    }

    protected function initHeader(): void
    {
        $this->header ??= [
            [
                [
                    'content' => $this->statusDropdown(),
                    'options' => ['class' => 'col-12 col-md-6 col-lg-4'],
                ],
                [
                    'content' => $this->getSearchInput(),
                    'options' => [
                        'class' => 'col-12 col-md-6 col-lg-4',
                    ],
                ],
                'options' => [
                    'class' => 'justify-content-between',
                ],
            ],
        ];
    }

    protected function initFooter(): void
    {
        /** @see TenantController::actionCreate() */
        $text = Html::iconText('plus', Yii::t('tenant', 'TENANT_CREATE_BUTTON'));
        $createButton = Html::a($text, ['/admin/tenant/create'], [
            'class' => 'btn btn-primary',
        ]);

        $this->footer = [
            [
                [
                    'content' => $createButton,
                    'visible' => Yii::$app->getUser()->can(Tenant::AUTH_TENANT_CREATE),
                    'options' => [
                        'class' => 'col-12',
                    ],
                ],
            ]
        ];
    }

    public function buttonsColumn(): array
    {
        return [
            'headerOptions' => ['class' => 'd-none d-md-table-cell'],
            'contentOptions' => ['class' => 'text-right d-none d-md-table-cell'],
            'content' => function (Tenant $tenant): string {
                $buttons = [];

                if (Yii::$app->get('tenant')->id != $tenant->id) {
                    $buttons[] = Html::a(Icon::tag('toggle-on'), Url::current(['tenant' => $tenant]), [
                        'class' => 'btn btn-secondary',
                        'title' => Yii::t('tenant', 'TENANT_SWITCH_ADMIN_BUTTON'),
                        'data-toggle' => 'tooltip',
                    ]);
                }

                if ($this->isSortedByPosition()) {
                    $buttons[] = $this->getSortableButton();
                }

                if (Yii::$app->getUser()->can(Tenant::AUTH_TENANT_UPDATE, ['tenant' => $tenant])) {
                    $buttons[] = $this->getUpdateButton($tenant);
                }

                return Html::buttons($buttons);
            }
        ];
    }

    protected function nameColumn(): array
    {
        return [
            'attribute' => 'name',
            'content' => function (Tenant $tenant) {
                $name = Html::markKeywords(Html::encode($tenant->name), $this->search);
                $url = $tenant->getAbsoluteUrl();

                return Html::a($name, $this->getRoute($tenant), ['class' => 'strong'])
                    . Html::tag('div', Html::a($url, $url, ['target' => '_blank']), ['class' => 'small']);
            },
        ];
    }

    protected function updatedAtColumn(): array
    {
        return [
            'class' => TimeagoColumn::class,
            'attribute' => 'updated_at',
            'displayAtBreakpoint' => 'lg',
        ];
    }
}
