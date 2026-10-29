<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Registry;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;

/**
 * The tenants' URLs answer where the installation runs, since nobody configures a console URL manager.
 */
class Report extends \Hirtz\Skeleton\Registry\Report
{
    #[Override]
    public function toArray(): array
    {
        $report = parent::toArray();
        $tenants = [];

        foreach (TenantCollection::getAll() as $tenant) {
            if ($tenant->url) {
                $tenants[] = [
                    'name' => $tenant->name,
                    'url' => $tenant->url,
                    'status' => $tenant->status,
                ];
            }
        }

        if ($tenants) {
            $report['extra'] = [...$report['extra'] ?? [], 'tenants' => $tenants];
        }

        return $report;
    }

    #[Override]
    protected function getUrl(): ?string
    {
        if ($this->url !== null) {
            return $this->url;
        }

        // in position order, so the default tenant answers where it has a URL
        foreach (TenantCollection::getAll() as $tenant) {
            if ($hostInfo = $tenant->getHostInfo()) {
                return $hostInfo;
            }
        }

        return parent::getUrl();
    }
}
