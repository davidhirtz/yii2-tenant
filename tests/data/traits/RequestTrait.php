<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\tests\data\traits;

use davidhirtz\yii2\skeleton\web\Request;
use Yii;

trait RequestTrait
{
    protected function getRequest($config = []): Request
    {
        Yii::$app->set('request', [
            'class' => Request::class,
            ...$config,
        ]);

        return Yii::$app->getRequest();
    }
}
