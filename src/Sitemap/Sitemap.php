<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Sitemap;

use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

/**
 * Every tenant has a sitemap of its own: the tenant is a variation whatever variations a project configures.
 */
class Sitemap extends \Hirtz\Skeleton\Sitemap\Sitemap
{
    #[Override]
    public function getVariations(): array
    {
        $variations = parent::getVariations();
        $manager = Yii::$app->getUrlManager();

        if ($manager instanceof UrlManager && $manager->tenant !== null) {
            $variations[] = "tenant-{$manager->tenant->id}";
        }

        return $variations;
    }
}
