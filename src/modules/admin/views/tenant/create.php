<?php

declare(strict_types=1);

/**
 * @see TenantController::actionCreate()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use Hirtz\Skeleton\helpers\Html;
use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\forms\FormContainer;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\modules\admin\controllers\TenantController;
use Hirtz\Tenant\modules\admin\widgets\forms\TenantActiveForm;
use Hirtz\Tenant\modules\admin\widgets\navs\TenantSubmenu;

$this->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'));

echo TenantSubmenu::make()
    ->model($tenant);

echo FormContainer::make()
    ->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'))
    ->form(TenantActiveForm::make()
        ->model($tenant));
