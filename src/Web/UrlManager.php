<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Web;

use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;
use yii\web\Cookie;

class UrlManager extends \Hirtz\Skeleton\Web\UrlManager
{
    public ?Tenant $tenant = null;

    #[Override]
    public function createAbsoluteUrl($params, $scheme = null): string
    {
        $tenant = $this->getTenantFromParams($params);

        if (!$tenant) {
            return parent::createAbsoluteUrl($params, $scheme);
        }

        $url = $this->createUrl($params);

        if (!str_contains($url, '://')) {
            $url = $tenant->getHostInfo() . $url;
        }

        return Url::ensureScheme($url, $scheme);
    }

    #[Override]
    public function createUrl($params): string
    {
        $params = (array)$params;

        if (!array_key_exists('tenant', $params)) {
            $params['tenant'] = Yii::$app->getRequest()->get('tenant');
        }

        $tenant = $this->getTenantFromParams($params, true);
        $url = parent::createUrl($params);

        if ($tenant) {
            $url = $tenant->getPathInfo() . $url;

            if ($tenant->getHostInfo() !== $this->tenant?->getHostInfo()) {
                $url = $tenant->getHostInfo() . $url;
            }
        }

        return $url;
    }

    #[Override]
    public function parseRequest($request): bool|array
    {
        $this->setTenantFromRequest($request);

        if ($this->tenant?->isDraft()) {
            Yii::$app->getResponse()->getHeaders()->set('X-Robots-Tag', 'none');
        }

        return parent::parseRequest($request);
    }

    public function getTenantFromRequest(Request $request): ?Tenant
    {
        $params = $request->getQueryParams();
        return $this->getTenantFromParams($params) ?? $this->getTenantFromUrl($request->getUrl());
    }

    protected function setTenantFromRequest(Request $request): void
    {
        $tenant = $this->getTenantFromUrl($request->getAbsoluteUrl());

        if ($tenant) {
            Yii::debug("Tenant found: $tenant->name", __METHOD__);
            $request->setPathInfo(substr($request->getPathInfo(), strlen($tenant->getPathInfo())));
            $this->setCookieDomain($tenant->getCookieDomain());
            $this->setTenant($tenant);
            return;
        }

        $tenant = TenantCollection::getDefault();

        if ($tenant) {
            Yii::debug("Tenant not found by host name or path info, using default tenant: $tenant->name");
            $this->setTenant($tenant);
        }
    }

    public function setTenant(Tenant $tenant): void
    {
        $this->tenant = $tenant;
        $this->setHostInfo($tenant->getHostInfo());
    }

    #[Override]
    protected function setLanguage(Request $request): void
    {
        if ($this->tenant) {
            $this->defaultLanguage = $this->tenant->language;
        }

        parent::setLanguage($request);
    }

    protected function setCookieDomain(string $domain): void
    {
        $definition = Yii::$container->getDefinitions()[Cookie::class];

        if (is_string($definition)) {
            $definition = ['class' => $definition];
        }

        $definition['domain'] ??= $domain;
        Yii::$container->set(Cookie::class, $definition);
    }

    protected function getTenantFromUrl(string $url): ?Tenant
    {
        return TenantCollection::getByUrl($url)
            ?? (
                strlen($url) > 6
                ? $this->getTenantFromUrl(substr($url, 0, strrpos($url, '/')))
                : null
            );
    }

    private function getTenantFromParams(array|string &$params, bool $remove = false): ?Tenant
    {
        $tenant = $params['tenant'] ?? null;

        if ($tenant instanceof Tenant) {
            if ($remove) {
                unset($params['tenant']);
            }

            return $tenant;
        }

        return $this->tenant;
    }
}
