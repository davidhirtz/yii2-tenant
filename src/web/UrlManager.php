<?php

namespace davidhirtz\yii2\tenant\web;

use davidhirtz\yii2\skeleton\helpers\Url;
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
        $tenant = $this->getTenantFromRequestUrl($request->getAbsoluteUrl());

        if (!$tenant) {
            Yii::debug('Tenant not found by host name or path info, using default tenant...');
            $tenant = current(TenantCollection::getAll());
        }

        if (!$tenant) {
            throw new InvalidConfigException();
        }

        Yii::debug("Tenant found: $tenant->name", __METHOD__);
        $this->setTenant($tenant);

        $this->defaultLanguage = $tenant->language;
        $request->setPathInfo(substr($request->getPathInfo(), strlen($tenant->getPathInfo())));

        return parent::parseRequest($request);
    }

    protected function setTenant(Tenant $tenant): void
    {
        Yii::$app->set('tenant', $tenant);
        $this->setTenantCookieDomain($tenant);
    }

    protected function setTenantCookieDomain(Tenant $tenant): void
    {
        $definition = Yii::$container->getDefinitions()[Cookie::class];

        if (is_string($definition)) {
            $definition = ['class' => $definition];
        }

        $definition['domain'] ??= $tenant->getCookieDomain();
        Yii::$container->set(Cookie::class, $definition);
    }

    private function getTenantFromRequestUrl(string $url): ?Tenant
    {
        return TenantCollection::getByUrl($url)
            ?? (
            strlen($url) > 6
                ? $this->getTenantFromRequestUrl(substr($url, 0, strrpos($url, '/')))
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
