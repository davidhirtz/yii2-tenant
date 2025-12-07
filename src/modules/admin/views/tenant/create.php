<?php

declare(strict_types=1);

/**
 * @see TenantController::actionCreate()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\forms\FormContainer;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\forms\TenantActiveForm;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;

$this->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'));

echo TenantSubmenu::make()
    ->model($tenant);

echo FormContainer::make()
    ->title(Yii::t('tenant', 'TENANT_TITLE_CREATE'))
    ->form(TenantActiveForm::make()
        ->model($tenant));
