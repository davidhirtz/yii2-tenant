<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin;

use Hirtz\Skeleton\Models\Breadcrumb;
use Hirtz\Skeleton\Modules\Admin\Module as AdminModule;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Widgets\Navs\Breadcrumbs;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Web\UrlManager;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantNavItem;
use Override;
use ReflectionMethod;
use Yii;
use yii\base\InlineAction;

/**
 * @property \Hirtz\Skeleton\Modules\Admin\Module $module
 */
class Module extends \Hirtz\Skeleton\Base\Module implements ModuleInterface
{
    public $defaultRoute = 'tenant';

    #[Override]
    public function dashboard(Dashboard $dashboard): Dashboard
    {
        $tenant = static::getCurrentTenant();

        if ($tenant) {
            $dashboard->getItem('homepage')?->url($tenant->getAbsoluteUrl());
        }

        return $dashboard;
    }

    /**
     * The admin runs under the tenant it shows, so the URL manager's tenant is the one switched to.
     */
    public static function getCurrentTenant(): ?Tenant
    {
        $manager = Yii::$app->getUrlManager();
        return $manager instanceof UrlManager ? $manager->tenant : null;
    }

    /**
     * A record belongs to one tenant, so an index keeps its route without parameters and any other page leads to the
     * dashboard. Neither carries `?tenant=` nor the content language of the tenant switched from.
     */
    public static function getSwitchUrl(Tenant $tenant): string
    {
        $action = Yii::$app->controller?->action;
        $route = $action && static::isSwitchableAction($action)
            ? ['/' . $action->getUniqueId()]
            : AdminModule::current()->getHomeRoute();

        return Yii::$app->getUrlManager()->createUrl([...$route, 'tenant' => $tenant]);
    }

    protected static function isSwitchableAction(\yii\base\Action $action): bool
    {
        if ($action->id !== 'index' || !$action instanceof InlineAction) {
            return false;
        }

        $method = new ReflectionMethod($action->controller, $action->actionMethod);
        return $method->getNumberOfRequiredParameters() === 0;
    }

    /**
     * Follows the site's name, the default tenant's, so it shows only for another tenant.
     */
    public static function addTenantBreadcrumb(Breadcrumbs $breadcrumbs): void
    {
        $tenant = static::getCurrentTenant();

        if (!$tenant || $tenant->id === TenantCollection::getDefault()?->id) {
            return;
        }

        $items = array_values($breadcrumbs->getBreadcrumbs());
        $home = $breadcrumbs->getHomeBreadcrumb();
        $position = $home ? array_search($home, $items, true) : false;

        array_splice($items, $position === false ? 0 : $position + 1, 0, [
            new Breadcrumb($tenant->name, $tenant->getAbsoluteUrl(), boost: false),
        ]);

        $breadcrumbs->breadcrumbs($items);
    }

    public function getName(): string
    {
        return Yii::t('tenant', 'TENANT_PLURAL');
    }

    #[Override]
    public function aside(Nav $nav): Nav
    {
        return $nav->addItem(TenantNavItem::make());
    }
}
