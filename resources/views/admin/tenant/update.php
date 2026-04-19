<?php

declare(strict_types=1);

/**
 * @see TenantController::actionUpdate()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use Hirtz\Skeleton\Web\View;
use Hirtz\Skeleton\Widgets\Forms\FormContainer;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Hirtz\Tenant\Modules\Admin\Widgets\Forms\TenantActiveForm;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantHeader;

echo TenantHeader::make()
    ->model($tenant);

echo FormContainer::make()
    ->title(Yii::t('tenant', 'TENANT_TITLE_UPDATE'))
    ->form(TenantActiveForm::make()
        ->model($tenant));
