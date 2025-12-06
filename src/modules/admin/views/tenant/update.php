<?php

declare(strict_types=1);

/**
 * @see TenantController::actionUpdate()
 * @see TenantController::actionDelete()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\forms\DeleteActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\FormContainer;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\forms\TenantActiveForm;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;


$this->title(Yii::t('tenant', 'TENANT_TITLE_UPDATE'));

echo TenantSubmenu::make()
    ->model($tenant);

echo FormContainer::make()
    ->title(Yii::t('tenant', 'TENANT_TITLE_UPDATE'))
    ->form(TenantActiveForm::make()
        ->model($tenant));

if ($tenant->isDeletable() && Yii::$app->getUser()->can(Tenant::AUTH_TENANT_DELETE, ['tenant' => $tenant])) {
    echo FormContainer::make()
        ->danger()
        ->title(Yii::t('tenant', 'TENANT_TITLE_DELETE'))
        ->form(DeleteActiveForm::make()
            ->model($tenant)
            ->property('name'));
}
