<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Web;

use Hirtz\Skeleton\Helpers\Url;
use Hirtz\Skeleton\Web\Application;
use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;
use yii\web\Cookie;

class UrlManager extends \Hirtz\Skeleton\Web\UrlManager
{
    public ?Tenant $tenant = null;

    /**
     * @param array<int|string, mixed>|string $params
     */
    #[Override]
    public function createAbsoluteUrl($params, $scheme = null): string
    {
        $tenant = $this->getTenantFromParams($params);
        $hostInfo = $tenant?->getHostInfo();

        if (!$hostInfo) {
            return parent::createAbsoluteUrl($params, $scheme);
        }

        $url = $this->createUrl($params);

        if (!str_contains($url, '://')) {
            $url = $hostInfo . $url;
        }

        return Url::ensureScheme($url, $scheme);
    }

    /**
     * @param array<int|string, mixed>|string $params
     */
    #[Override]
    public function createUrl($params): string
    {
        $params = (array)$params;

        if (!array_key_exists('tenant', $params)) {
            // A console command builds URLs too — a mailed reset link, a sitemap — and there is no request to
            // read the current tenant from there.
            $params['tenant'] = Request::current()?->get('tenant');
        }

        $tenant = $this->getTenantFromParams($params, true);
        $url = parent::createUrl($params);

        if ($tenant) {
            $url = $tenant->getPathInfo() . $url;
            $hostInfo = $tenant->getHostInfo();

            if ($hostInfo && $hostInfo !== $this->tenant?->getHostInfo()) {
                $url = $hostInfo . $url;
            }
        }

        return $url;
    }

    /**
     * @return array{string, array<string, mixed>}|false
     */
    #[Override]
    public function parseRequest($request): bool|array
    {
        $this->setTenantFromRequest($request);

        if ($this->tenant?->isDraft()) {
            Application::current()->getResponse()->getHeaders()->set('X-Robots-Tag', 'none');
        }

        $result = parent::parseRequest($request);

        // The parent resets the host to the request's; the tenant's canonical host has to win, also on a host that
        // only fell back to the default tenant. A tenant without a URL names none, so the request's host stands.
        if ($hostInfo = $this->tenant?->getHostInfo()) {
            $this->setHostInfo($hostInfo);
        }

        return $result;
    }

    public function getTenantFromRequest(Request $request): ?Tenant
    {
        $params = $request->getQueryParams();
        return $this->getTenantFromParams($params) ?? $this->getTenantFromUrl($request->getUrl());
    }

    protected function setTenantFromRequest(Request $request): void
    {
        $cookieDomain = TenantCollection::getCookieDomainByHostInfo((string)$request->getHostInfo());

        if ($cookieDomain) {
            $this->setCookieDomain($cookieDomain);
        }

        $tenant = $this->getTenantFromUrl($request->getAbsoluteUrl());

        if ($tenant) {
            Yii::debug("Tenant found: $tenant->name", __METHOD__);
            $request->setPathInfo(substr($request->getPathInfo(), strlen($tenant->getPathInfo())));

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
        $hostInfo = $tenant->getHostInfo();

        if ($hostInfo) {
            $this->setHostInfo($hostInfo);
        }
    }

    /**
     * A tenant without a language pins none, as one without a URL pins no host — the configured `defaultLanguage`
     * stands, rather than being wiped by the empty column.
     */
    #[Override]
    protected function setLanguage(Request $request): void
    {
        if ($this->tenant?->language) {
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
        $tenant = TenantCollection::getByUrl($url);

        if ($tenant || strlen($url) <= 6) {
            return $tenant;
        }

        $position = strrpos($url, '/');

        return $position !== false ? $this->getTenantFromUrl(substr($url, 0, $position)) : null;
    }

    /**
     * @param array<int|string, mixed>|string $params
     */
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

    #[Override]
    public function getDraftHostInfo(): string
    {
        $hostInfo = $this->tenant?->getHostInfo();

        if ($hostInfo) {
            return $this->draftSubdomain
                ? $this->replaceSubdomain($this->draftSubdomain, $hostInfo)
                : $hostInfo;
        }

        return parent::getDraftHostInfo();
    }
}
