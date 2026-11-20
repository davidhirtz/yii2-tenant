<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin;

use Hirtz\Skeleton\Modules\Admin\Module as AdminModule;
use Hirtz\Skeleton\Modules\Admin\ModuleInterface;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\Request;
use Hirtz\Skeleton\Widgets\Navs\Nav;
use Hirtz\Skeleton\Widgets\Panels\Dashboard;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantSwitchInterface;
use Hirtz\Tenant\Web\UrlManager;
use Hirtz\Tenant\Modules\Admin\Widgets\Navs\TenantNavItem;
use Override;
use yii\base\ActionEvent;
use yii\web\ErrorAction;
use Yii;

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
     * The page's route and query on the other tenant, unless its controller names another route
     * ({@see TenantSwitchInterface}), without a `?tenant=` naming the tenant switched from.
     */
    public static function getSwitchUrl(Tenant $tenant): string
    {
        return Yii::$app->getUrlManager()->createUrl([...static::getSwitchRoute(), 'tenant' => $tenant]);
    }

    /**
     * @return array<int|string, mixed>
     */
    protected static function getSwitchRoute(): array
    {
        $action = Yii::$app->controller?->action;
        $request = Request::current();
        $home = AdminModule::current()->getHomeRoute();

        // A page answering a POST, such as a grid swapped after a status change, or an error is no page to link to.
        if (!$action || !$request?->getIsGet() || $action instanceof ErrorAction) {
            return $home;
        }

        if ($action->controller instanceof TenantSwitchInterface) {
            return $action->controller->getTenantSwitchRoute($action) ?? $home;
        }

        $params = $request->getQueryParams();
        unset($params['tenant']);

        return ['/' . $action->getUniqueId(), ...$params];
    }

    /**
     * htmx swaps `#wrap` alone, so a link into another tenant would leave the navbar on the tenant left.
     */
    public static function reloadOtherTenant(ActionEvent $event): void
    {
        $request = Request::current();
        $manager = Yii::$app->getUrlManager();

        if (!$request?->isHtmxRequest() || !$request->getIsGet() || !$manager instanceof UrlManager) {
            return;
        }

        $url = $request->getHeaders()->get('HX-Current-URL');

        if (!is_string($url)) {
            return;
        }

        $tenant = $manager->getTenantFromUrl($url) ?? TenantCollection::getDefault();

        if ($tenant?->id === $manager->tenant?->id) {
            return;
        }

        $response = Application::current()->getResponse();
        $response->getHeaders()->set('HX-Redirect', $request->getAbsoluteUrl());
        $response->setHtmxReload();

        $event->isValid = false;
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
