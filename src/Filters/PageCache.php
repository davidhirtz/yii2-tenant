<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Filters;

use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

/**
 * The tenant is added to the key itself rather than to the variations, which a project may replace or configure as
 * a callable: a page cached for one tenant must never be served on another tenant's host.
 */
class PageCache extends \Hirtz\Skeleton\Filters\PageCache
{
    /**
     * @return array<array-key, mixed>
     */
    #[Override]
    protected function calculateCacheKey(): array
    {
        $key = parent::calculateCacheKey();
        $manager = Yii::$app->getUrlManager();

        if ($manager instanceof UrlManager && $manager->tenant !== null) {
            $key[] = "tenant-{$manager->tenant->id}";
        }

        return $key;
    }
}
