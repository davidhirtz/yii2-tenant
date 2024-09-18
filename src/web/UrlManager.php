<?php

namespace davidhirtz\yii2\tenant\web;

use davidhirtz\yii2\skeleton\helpers\Url;
use davidhirtz\yii2\skeleton\web\Request;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\models\Tenant;
use Yii;
use yii\base\InvalidConfigException;
use yii\web\Cookie;

class UrlManager extends \davidhirtz\yii2\skeleton\web\UrlManager
{
    public function init(): void
    {
        $this->i18nUrl = false;
        $this->i18nSubdomain = false;

        parent::init();
    }

    public function createAbsoluteUrl($params, $scheme = null): string
    {
        $tenant = $this->getTenantFromParams($params);
        $url = $this->createUrl($params);

        if (!str_contains($url, '://')) {
            $url = $tenant->getHostInfo() . $url;
        }

        return Url::ensureScheme($url, $scheme);
    }

    public function createDraftUrl(array|string $params): string
    {
        $tenant = $this->getTenantFromParams($params, true);
        return Url::draft($tenant->getHostInfo()) . $this->createUrl($params);
    }

    public function createUrl($params): string
    {
        $tenant = $this->getTenantFromParams($params, true);

        $url = parent::createUrl($params);
        $url = $tenant->getPathInfo() . $url;

        if ($tenant->getHostInfo() !== Yii::$app->get('tenant')->getHostInfo()) {
            $url = $tenant->getHostInfo() . $url;
        }

        return $url;
    }

    public function parseRequest($request): bool|array
    {
        $tenant = $this->getTenantFromRequest($request);
        $this->setTenant($tenant);

        $this->defaultLanguage = $tenant->language;
        $request->setPathInfo(substr($request->getPathInfo(), strlen($tenant->getPathInfo())));

        return parent::parseRequest($request);
    }

    protected function setTenant(Tenant $tenant): void
    {
        Yii::$app->set('tenant', $tenant);
    }

    protected function insertDefaultTenant(): Tenant
    {
        $tenant = Tenant::create();
        $tenant->loadDefaultValues();
        $tenant->name = Yii::t('tenant', 'Default');
        $tenant->url = Yii::$app->getRequest()->getHostInfo();
        $tenant->language = Yii::$app->language;

        if (!$tenant->insert()) {
            $error = current($tenant->getFirstErrors());
            throw new InvalidConfigException("Could not create default tenant: $error");
        }

        return $tenant;
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
            $this->setCookieDomain($tenant->getCookieDomain());
            return $tenant;
        }

        $tenant = current(TenantCollection::getAll());

        if ($tenant) {
            Yii::debug("Tenant not found by host name or path info, using default tenant $tenant->name...");
            $this->setCookieDomain($request->getHostName());
            return $tenant;
        }

        return $this->insertDefaultTenant();
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
