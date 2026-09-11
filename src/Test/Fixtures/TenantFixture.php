<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Test\Fixtures;

use Hirtz\Skeleton\Test\Fixtures\ActiveFixture;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;

class TenantFixture extends ActiveFixture
{
    public $modelClass = Tenant::class;

    /**
     * Every migrated database carries a seeded tenant, so its row has to go before the fixture writes its own ids.
     * The collection caches in a static, which outlives the application a test case builds.
     */
    #[Override]
    public function load(): void
    {
        $this->resetTable();
        parent::load();

        TenantCollection::invalidateCache();
    }

    #[Override]
    public function unload(): void
    {
        parent::unload();

        TenantCollection::invalidateCache();
    }
}
