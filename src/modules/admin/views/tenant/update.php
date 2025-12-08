<?php

declare(strict_types=1);

/**
 * @see TenantController::actionUpdate()
 * @see TenantController::actionDelete()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use Hirtz\Skeleton\web\View;
use Hirtz\Skeleton\widgets\forms\DeleteActiveForm;
use Hirtz\Skeleton\widgets\forms\FormContainer;
use Hirtz\Tenant\models\Tenant;
use Hirtz\Tenant\modules\admin\controllers\TenantController;
use Hirtz\Tenant\modules\admin\widgets\forms\TenantActiveForm;
use Hirtz\Tenant\modules\admin\widgets\navs\TenantSubmenu;

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
