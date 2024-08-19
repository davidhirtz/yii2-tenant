<?php
/**
 * @see TenantController::actionIndex()
 *
 * @var View $this
 * @var ActiveDataProvider $provider
 */

use davidhirtz\yii2\skeleton\web\View;
use davidhirtz\yii2\skeleton\widgets\bootstrap\Panel;
use davidhirtz\yii2\tenant\modules\admin\controllers\TenantController;
use davidhirtz\yii2\tenant\modules\admin\widgets\grids\TenantGridView;
use davidhirtz\yii2\tenant\modules\admin\widgets\navs\TenantSubmenu;
use yii\data\ActiveDataProvider;

$this->setTitle(Yii::t('tenant', 'TENANT_NAME_PLURAL'));
?>

<?= TenantSubmenu::widget(); ?>

<?= Panel::widget([
    'content' => TenantGridView::widget([
        'dataProvider' => $provider,
    ]),
]); ?>