<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\UrlManager;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Module;
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

        $app->getI18n()->translations['tenant'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@tenant/../messages',
            'forceTranslation' => true,
        ];

        if (!Yii::$container->has(UrlManager::class)) {
            Yii::$container->set(UrlManager::class, Web\UrlManager::class);
        }

        $app->extendModule('admin', [
            'modules' => [
                'tenant' => [
                    'class' => Module::class
                ],
            ],
        ]);

        DashboardController::addRoles([
            Tenant::AUTH_TENANT_UPDATE,
        ]);

        $app->setMigrationNamespace('Hirtz\Tenant\Migrations');
    }
}
