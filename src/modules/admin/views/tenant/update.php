<?php
/**
 * @see TenantController::actionUpdate()
 * @see TenantController::actionDelete()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use davidhirtz\yii2\skeleton\widgets\forms\DeleteActiveForm;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\forms\TenantActiveForm;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;

$this->setTitle(Yii::t('tenant', 'TENANT_TITLE_UPDATE'));
?>

<?= TenantSubmenu::widget([
    'model' => $tenant->tenant,
]); ?>

<?= Html::errorSummary($tenant); ?>

<?= Panel::widget([
    'title' => Yii::t('tenant', 'TENANT_TITLE_UPDATE'),
    'content' => TenantActiveForm::widget([
        'form' => $tenant,
    ]),
]); ?>

<?php if ($tenant->tenant->isDeletable()) {
    echo Panel::widget([
        'type' => 'danger',
        'title' => Yii::t('tenant', 'TENANT_TITLE_DELETE'),
        'content' => DeleteActiveForm::widget([
            'model' => $tenant->tenant,
        ]),
    ]);
} ?>
