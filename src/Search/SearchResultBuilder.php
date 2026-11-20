<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Search;

use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\SearchableInterface;
use Hirtz\Skeleton\Search\SearchResult;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Modules\Admin\Module;
use Override;

class SearchResultBuilder extends \Hirtz\Skeleton\Search\SearchResultBuilder
{
    /**
     * A record of another tenant is named by its tenant and opens in that tenant's admin.
     */
    #[Override]
    protected function createSearchResult(ActiveRecord&SearchableInterface $record): ?SearchResult
    {
        $result = parent::createSearchResult($record);
        $current = Module::getCurrentTenant();

        if (!$result || !$current) {
            return $result;
        }

        $tenant = TenantCollection::getById($record->getSearchTenantId());

        if (!$tenant || $tenant->id === $current->id) {
            return $result;
        }

        $result = $result->withBadge($tenant->name);

        return $result->route ? $result->withRoute([...$result->route, 'tenant' => $tenant]) : $result;
    }
}
