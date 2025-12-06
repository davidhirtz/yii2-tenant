<?php

declare(strict_types=1);

/**
 * @see TenantController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 */

use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\grids\GridContainer;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\grids\TenantGridView;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;
use yii\data\ActiveDataProvider;

$this->title(Yii::t('tenant', 'TENANT_NAME_PLURAL'));

echo TenantSubmenu::make();

echo GridContainer::make()
    ->grid(TenantGridView::make()
        ->provider($provider));
