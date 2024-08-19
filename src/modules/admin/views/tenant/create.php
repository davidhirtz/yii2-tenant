<?php
/**
 * @see TenantController::actionCreate()
 *
 * @var View $this
 * @var Tenant $tenant
 */

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\forms\TenantActiveForm;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;

$this->setTitle(Yii::t('tenant', 'TENANT_TITLE_CREATE'));
?>

<?= TenantSubmenu::widget([
    'model' => $tenant,
]); ?>

<?= Html::errorSummary($tenant); ?>

<?= Panel::widget([
    'title' => Yii::t('tenant', 'TENANT_TITLE_CREATE'),
    'content' => TenantActiveForm::widget([
        'model' => $tenant,
    ]),
]); ?>