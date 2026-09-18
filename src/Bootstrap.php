<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Registry\Report;
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
     * @param Application<User> $app
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

        // Only the slot: a project re-pointing the report in its own configuration keeps its class.
        if (!Yii::$container->has(Report::class)) {
            Yii::$container->set(Report::class, Registry\Report::class);
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

            DashboardController::addRoles(static fn (): array => [
                Tenant::AUTH_TENANT,
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
