<?php

declare(strict_types=1);

/**
 * @see TenantController::actionCreate()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use Hirtz\Skeleton\Helpers\Html;
use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Hirtz\Tenant\Modules\Admin\Widgets\Forms\TenantActiveForm;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantSubmenu;

$this->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'));

echo TenantSubmenu::make()
    ->model($tenant);

echo FormContainer::make()
    ->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'))
    ->form(TenantActiveForm::make()
        ->model($tenant));
