<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

use Hirtz\Skeleton\Base\ConfigBootstrapInterface;
use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Registry\Report;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\Request;
use Hirtz\Skeleton\Web\UrlManager;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;
use yii\i18n\PhpMessageSource;

class Bootstrap implements ConfigBootstrapInterface
{
    #[Override]
    public static function getDefaultConfig(): array
    {
        return [
            'components' => [
                'i18n' => [
                    'translations' => [
                        'tenant' => [
                            'class' => PhpMessageSource::class,
                            'basePath' => '@tenant/../messages',
                            'forceTranslation' => true,
                        ],
                    ],
                ],
                'sitemap' => [
                    'variations' => static function () {
                        $manager = Yii::$app->getUrlManager();
                        return $manager instanceof Web\UrlManager ? $manager->tenant?->id : null;
                    },
                ],
            ],
            'container' => [
                'definitions' => [
                    PageCache::class => Filters\PageCache::class,
                    Report::class => Registry\Report::class,
                    UrlManager::class => Web\UrlManager::class,
                ],
            ],
            'modules' => [
                'tenant' => [
                    'class' => Module::class,
                ],
            ],
        ];
    }

    /**
     * @param Application<User> $app
     */
    public function bootstrap($app): void
    {
        Yii::setAlias('@tenant', __DIR__);
        TenantCollection::reset();

        $app->on(Application::EVENT_BEFORE_REQUEST, static function (): void {
            if ($request = Request::current()) {
                TenantCollection::addAllowedHosts($request);
            }
        });

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
