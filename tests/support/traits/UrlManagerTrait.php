<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\support\traits;

use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\web\UrlManager;
use Yii;

trait UrlManagerTrait
{
    protected function getUrlManager($config = []): UrlManager
    {
        Yii::$app->set('urlManager', [
            'class' => UrlManager::class,
            'tenant' => TenantCollection::getDefault(),
            ...$config,
        ]);

        /** @var UrlManager $manager */
        $manager = Yii::$app->getUrlManager();
        return $manager;
    }
}
