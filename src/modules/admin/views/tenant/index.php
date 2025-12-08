<?php

declare(strict_types=1);

/**
 * @see TenantController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 */

use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Grids\GridContainer;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Hirtz\Tenant\Modules\Admin\Widgets\Grids\TenantGridView;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantSubmenu;
use yii\data\ActiveDataProvider;

$this->title(Yii::t('tenant', 'TENANT_NAME_PLURAL'));

echo TenantSubmenu::make();

echo GridContainer::make()
    ->grid(TenantGridView::make()
        ->provider($provider));
