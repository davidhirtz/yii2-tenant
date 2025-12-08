<?php

declare(strict_types=1);

namespace Hirtz\Tenant\models\actions;

use Hirtz\Skeleton\Models\Actions\ReorderActiveRecords;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Tenant\models\collections\TenantCollection;
use Hirtz\Tenant\models\Tenant;
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
