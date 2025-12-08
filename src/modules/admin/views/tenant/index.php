<?php

declare(strict_types=1);

/**
 * @see TenantController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 */

use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\grids\GridContainer;
use Hirtz\Tenant\modules\admin\controllers\TenantController;
use Hirtz\Tenant\modules\admin\widgets\grids\TenantGridView;
use Hirtz\Tenant\modules\admin\widgets\navs\TenantSubmenu;
use yii\data\ActiveDataProvider;

$this->title(Yii::t('tenant', 'TENANT_NAME_PLURAL'));

echo TenantSubmenu::make();

echo GridContainer::make()
    ->grid(TenantGridView::make()
        ->provider($provider));
