<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Actions;

use Hirtz\Skeleton\Models\Actions\ReorderActiveRecords;
use Hirtz\Skeleton\I18n\Message;
use Hirtz\Skeleton\Models\Trail;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;

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
        Trail::createOrderTrail(null, Message::make('tenant', 'TENANT_TRAIL_REORDERED'));
        TenantCollection::invalidateCache();

        parent::afterReorder();
    }
}
