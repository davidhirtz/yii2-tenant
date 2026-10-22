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
     * @var array<string, string>|null
     */
    private ?array $applicationLanguages = null;

    private ?string $applicationDefaultLanguage = null;

    #[Override]
    public function init(): void
    {
        $this->applicationDefaultLanguage = $this->defaultLanguage;
        parent::init();
    }

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
     * On the default tenant, whichever one the request is on.
     *
     * @param array<int|string, mixed>|string $params
     */
    #[Override]
    public function createCanonicalUrl(array|string $params): string
    {
        $tenant = TenantCollection::getDefault();
        return parent::createCanonicalUrl($tenant ? [...(array)$params, 'tenant' => $tenant] : $params);
    }

    /**
     * @param array<int|string, mixed>|string $params
     */
    #[Override]
    public function createUrl($params): string
    {
        $params = (array)$params;

        if (!array_key_exists('tenant', $params)) {
            // Only a tenant the query names, never the raw value a cached page would carry into every link (#496).
            $params['tenant'] = TenantCollection::getFromRequest()?->id;
        }

        $tenant = $this->getTenantFromParams($params, true);

        // Another tenant's URL carries that tenant's language prefixes, not the request's.
        $url = $tenant && $tenant->id !== $this->tenant?->id
            ? $this->callbackWithTenantLanguages($tenant, fn (): string => parent::createUrl($params))
            : parent::createUrl($params);

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

        // The parent reads a language only from two on, and a single-language tenant has one
        if (count($this->getLanguages()) === 1) {
            Yii::$app->language = (string)array_key_first($this->getLanguages());
        }

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

        $this->setTenantLanguages($tenant);
    }

    /**
     * Narrows the map that routing, browser detection, hreflang tags and sitemaps read, the property included.
     */
    protected function setTenantLanguages(Tenant $tenant): void
    {
        $this->applicationLanguages ??= $this->getLanguages();
        $this->defaultLanguage = $tenant->language ?? $this->applicationDefaultLanguage;
        $this->languages = array_intersect_key($this->applicationLanguages, array_flip($tenant->getAvailableLanguages()));

        // A single language is the default: nothing parses its prefix.
        if (count($this->languages) === 1) {
            $this->defaultLanguage = (string)array_key_first($this->languages);
        }
    }

    /**
     * @template T
     * @param callable(): T $callback
     * @return T
     */
    protected function callbackWithTenantLanguages(Tenant $tenant, callable $callback): mixed
    {
        $languages = $this->languages;
        $defaultLanguage = $this->defaultLanguage;

        $this->setTenantLanguages($tenant);

        try {
            return $callback();
        } finally {
            $this->languages = $languages;
            $this->defaultLanguage = $defaultLanguage;
        }
    }

    /**
     * The configured `defaultLanguage`, which a tenant's own replaces for the request.
     */
    public function getApplicationDefaultLanguage(): ?string
    {
        return $this->applicationDefaultLanguage;
    }

    /**
     * Without a prefix to read, the browser picks among the tenant's languages, falling back to the default.
     */
    #[Override]
    protected function setLanguage(Request $request): void
    {
        if ($this->i18nUrl) {
            parent::setLanguage($request);
            return;
        }

        $languages = array_keys($this->getLanguages());

        if ($this->defaultLanguage !== null) {
            $languages = array_values(array_unique([$this->defaultLanguage, ...$languages]));
        }

        Yii::$app->language = $request->getPreferredLanguage($languages);
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
