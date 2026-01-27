<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\web;

use davidhirtz\yii2\skeleton\helpers\Url;
use davidhirtz\yii2\skeleton\web\Request;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\models\Tenant;
use Override;
use Yii;
use yii\web\Cookie;

class UrlManager extends \davidhirtz\yii2\skeleton\web\UrlManager
{
    #[Override]
    public function createAbsoluteUrl($params, $scheme = null): string
    {
        $tenant = $this->getTenantFromParams($params);
        $url = $this->createUrl($params);

        if (!str_contains($url, '://')) {
            $url = $tenant->getHostInfo() . $url;
        }

        return Url::ensureScheme($url, $scheme);
    }

    #[Override]
    public function createUrl($params): string
    {
        $tenant = $this->getTenantFromParams($params, true);

        $url = parent::createUrl($params);

        if (!is_string($params) || !str_starts_with($params, $tenant->getPathInfo())) {
            $url = $tenant->getPathInfo() . $url;
        }

        if ($tenant->getHostInfo() !== Yii::$app->get('tenant')->getHostInfo()) {
            $url = $tenant->getHostInfo() . $url;
        }

        return $url;
    }

    #[Override]
    public function parseRequest($request): bool|array
    {
        $tenant = $this->getTenantFromRequest($request);
        $this->setTenant($tenant);

        if ($tenant->isDraft()) {
            Yii::$app->getResponse()->getHeaders()->set('X-Robots-Tag', 'none');
        }

        return parent::parseRequest($request);
    }

    public function setTenant(Tenant $tenant): void
    {
        Yii::$app->set('tenant', $tenant);
        Yii::$app->language = $tenant->language;

        $this->setHostInfo($tenant->getHostInfo());
    }

    #[Override]
    protected function setApplicationLanguage(Request $request): void
    {
        $this->defaultLanguage = Yii::$app->get('tenant')->language;
        parent::setApplicationLanguage($request);
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

    protected function getTenantFromRequest(Request $request): Tenant
    {
        $tenant = $this->getTenantFromUrl($request->getAbsoluteUrl());

        if ($tenant) {
            Yii::debug("Tenant found: $tenant->name", __METHOD__);
            $request->setPathInfo(substr($request->getPathInfo(), strlen($tenant->getPathInfo())));
            $this->setCookieDomain($tenant->getCookieDomain());

            return $tenant;
        }

        $tenant = TenantCollection::getDefault();

        Yii::debug("Tenant not found by host name or path info, using tenant: $tenant->name", __METHOD__);
        return $tenant;
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

    private function getTenantFromParams(array|string &$params, bool $remove = false): Tenant
    {
        $tenant = $params['tenant'] ?? null;

        if ($tenant instanceof Tenant) {
            if ($remove) {
                unset($params['tenant']);
            }

            return $tenant;
        }

        return Yii::$app->get('tenant');
    }
}
