<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Filters;

use Hirtz\Tenant\Web\UrlManager;
use Override;
use Yii;

class PageCache extends \Hirtz\Skeleton\Filters\PageCache
{
    #[Override]
    public function init(): void
    {
        parent::init();

        if (!is_callable($this->variations)) {
            $manager = Yii::$app->getUrlManager();

            if ($manager instanceof UrlManager && $manager->tenant !== null) {
                $this->variations[] = $manager->tenant->id;
            }
        }
    }
}
