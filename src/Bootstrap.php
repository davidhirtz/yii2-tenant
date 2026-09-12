<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\UrlManager;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
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
        TenantCollection::reset();

        $app->getI18n()->translations['tenant'] ??= [
            'class' => PhpMessageSource::class,
            'basePath' => '@tenant/../messages',
            'forceTranslation' => true,
        ];

        if (!Yii::$container->has(UrlManager::class)) {
            Yii::$container->set(UrlManager::class, Web\UrlManager::class);
        }

        if (!Yii::$container->has(PageCache::class)) {
            Yii::$container->set(PageCache::class, Filters\PageCache::class);
        }

        $app->extendModule('tenant', [
            'class' => Module::class,
        ]);

        $app->extendComponent('sitemap', [
            'variations' => function () {
                $manager = Yii::$app->getUrlManager();
                return $manager instanceof Web\UrlManager ? $manager->tenant?->id : null;
            },
        ]);

        if ($this->isAdminModuleEnabled()) {
            $app->extendModule('admin', [
                'modules' => [
                    'tenant' => [
                        'class' => Modules\Admin\Module::class,
                    ],
                ],
            ]);

            DashboardController::addRoles([
                Tenant::AUTH_TENANT_UPDATE,
            ]);
        }

        $app->setMigrationNamespace('Hirtz\Tenant\Migrations');
    }

    /**
     * @see Module::$enableAdminModule
     */
    protected function isAdminModuleEnabled(): bool
    {
        return (bool)(Yii::$app->getModules()['tenant']['enableAdminModule'] ?? true);
    }
}
