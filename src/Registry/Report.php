<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Registry;

use Hirtz\Tenant\Models\Collections\TenantCollection;
use Override;

/**
 * The tenants' URLs are the better answer to "where does this installation run" than a console URL manager
 * nobody configures: the first tenant with a URL — the default one, where it has one — gives the report its
 * `url`, and every tenant with one is listed under `extra.tenants`. `--url` still wins, and an installation
 * whose tenants carry no URL falls back to the skeleton's own resolution.
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
