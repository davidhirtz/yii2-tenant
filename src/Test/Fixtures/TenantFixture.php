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
     * The seeded tenant goes before the fixture writes its ids, and the collection's static outlives the application.
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
