<?php

namespace davidhirtz\yii2\tenant\models\actions;

use davidhirtz\yii2\skeleton\models\actions\ReorderActiveRecords;
use davidhirtz\yii2\skeleton\models\Trail;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;

/**
 * @extends ReorderActiveRecords<Tenant>
 */
class ReorderTenants extends ReorderActiveRecords
{
    public function __construct(array $tenantIds)
    {
        $entries = Tenant::find()
            ->select(['id', 'position'])
            ->andWhere(['id' => $tenantIds])
            ->orderBy(['position' => SORT_ASC])
            ->all();

        $order = array_flip($tenantIds);

        parent::__construct($entries, $order);
    }

    protected function afterReorder(): void
    {
        Trail::createOrderTrail(null, Yii::t('tenant', 'TENANT_TRAIL_REORDERED'));
        TenantCollection::invalidateCache();

        parent::afterReorder();
    }
}
