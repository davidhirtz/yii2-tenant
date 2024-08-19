<?php

namespace davidhirtz\yii2\tenant\modules\admin\widgets\navs;

use davidhirtz\yii2\skeleton\helpers\Html;
use davidhirtz\yii2\skeleton\widgets\fontawesome\Submenu;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;

class TenantSubmenu extends Submenu
{
    public ?Tenant $model = null;

    public function init(): void
    {
        $this->title ??= Html::a(Yii::t('tenant', 'TENANT_NAME_PLURAL'), ['/admin/tenant/']);
        $this->getView()->setBreadcrumb(Yii::t('tenant', 'TENANT_NAME_PLURAL'), ['/admin/tenant/']);

        $this->items = [
            'tenant' => [
                'label' => Yii::t('tenant', 'TENANT_NAME_PLURAL'),
                'icon' => 'landmark',
                'roles' => [Tenant::AUTH_TENANT_UPDATE],
                'url' => ['/admin/tenant'],
                'active' => [
                    'admin/tenant/',
                ],
            ],
        ];

        parent::init();
    }
}
