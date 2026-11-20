<?php

declare(strict_types=1);

namespace Hirtz\Tenant;

use Hirtz\Skeleton\Base\ConfigBootstrapInterface;
use Hirtz\Skeleton\Filters\PageCache;
use Hirtz\Skeleton\Helpers\EventHelper;
use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Modules\Admin\Controllers\DashboardController;
use Hirtz\Skeleton\Modules\Admin\Module as AdminModule;
use Hirtz\Skeleton\Modules\Admin\Widgets\HostAlert as BaseHostAlert;
use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\NavBar;
use Hirtz\Skeleton\Modules\Admin\Widgets\Navs\SearchHeader;
use Hirtz\Skeleton\Registry\Report;
use Hirtz\Skeleton\Search\SearchResultBuilder;
use Hirtz\Skeleton\Sitemap\Sitemap as BaseSitemap;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\Request;
use Hirtz\Skeleton\Web\User as WebUser;
use Hirtz\Skeleton\Web\UrlManager;
use Hirtz\Skeleton\Widgets\Widget;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;
use yii\base\ActionEvent;
use yii\base\Module as BaseModule;
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
            ],
            'container' => [
                'definitions' => [
                    BaseHostAlert::class => Modules\Admin\Widgets\HostAlert::class,
                    PageCache::class => Filters\PageCache::class,
                    Report::class => Registry\Report::class,
                    SearchResultBuilder::class => Search\SearchResultBuilder::class,
                    BaseSitemap::class => Sitemap\Sitemap::class,
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
    #[Override]
    public function bootstrap($app): void
    {
        Yii::setAlias('@tenant', __DIR__);
        TenantCollection::reset();

        $app->on(Application::EVENT_BEFORE_REQUEST, static function (): void {
            if ($request = Request::current()) {
                TenantCollection::addAllowedHosts($request);
            }

            if ($webuser = WebUser::current()) {
                $webuser->authHostInfo ??= static fn (): ?string => TenantCollection::getDefault()?->getHostInfo();
                $webuser->handoffHostInfos = $webuser->handoffHostInfos
                    ?: static fn (): array => TenantCollection::getHandoffHostInfos($webuser->getAuthHostInfo());
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

            EventHelper::on(
                AdminModule::class,
                BaseModule::EVENT_BEFORE_ACTION,
                static fn (AdminModule $module, ActionEvent $event) => Modules\Admin\Module::reloadOtherTenant($event),
                ActionEvent::class,
            );

            EventHelper::on(SearchHeader::class, Widget::EVENT_CONFIGURE, static function (SearchHeader $header): void {
                $header->addContent(Modules\Admin\Widgets\Grids\Toolbars\TenantFilterDropdown::make());
            });

            EventHelper::on(NavBar::class, Widget::EVENT_CONFIGURE, static function (NavBar $navBar): void {
                $navBar->addItem(Modules\Admin\Widgets\Buttons\TenantDropdownButton::make());
            });
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
