<?php

namespace davidhirtz\yii2\tenant;

use davidhirtz\yii2\skeleton\web\Application;
use davidhirtz\yii2\skeleton\web\UrlManager;
use davidhirtz\yii2\tenant\modules\admin\Module;
use Yii;
use yii\base\BootstrapInterface;
use yii\i18n\PhpMessageSource;

class Bootstrap implements BootstrapInterface
{
    /**
     * @param Application $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@tenant', __DIR__);

        $app->extendComponents([
            'i18n' => [
                'translations' => [
                    'tenant' => [
                        'class' => PhpMessageSource::class,
                        'basePath' => '@tenant/messages',
                        'forceTranslation' => true,
                    ],
                ],
            ],
        ]);

        if (!Yii::$container->has(UrlManager::class)) {
            Yii::$container->set(UrlManager::class, web\UrlManager::class);
        }

        $app->extendModule('admin', [
            'modules' => [
                'tenant' => [
                    'class' => Module::class
                ],
            ],
        ]);

        $app->setMigrationNamespace('davidhirtz\yii2\tenant\migrations');
    }
}
