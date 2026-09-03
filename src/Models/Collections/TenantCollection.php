<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models\Collections;

use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Web\UrlManager;
use Yii;
use yii\caching\TagDependency;

class TenantCollection
{
    public const string CACHE_KEY = 'tenant-collection';

    protected static ?array $_tenants = null;

    /**
     * @return array<int, Tenant>
     */
    public static function getAll(): array
    {
        return static::$_tenants ??= static::findAll();
    }

    public static function getDefault(): ?Tenant
    {
        $tenants = static::getAll();
        return $tenants ? reset($tenants) : null;
    }

    /**
     * @return array<int, Tenant>
     */
    public static function getVisibleTenants(): array
    {
        $manager = Yii::$app->getUrlManager();
        $tenant = $manager instanceof UrlManager ? $manager->tenant : null;
        $tenant ??= static::getDefault();

        return array_filter(static::getAll(), fn (Tenant $current) => $current->status >= ($tenant->status ?? 0));
    }

    public static function getByUrl(string $url): ?Tenant
    {
        $matches = [$url];

        $draftDomain = Yii::$app->getUrlManager()->draftSubdomain;

        if ($draftDomain && str_contains($url, "//$draftDomain")) {
            $matches[] = str_replace("//$draftDomain", '//www', $url);
            $matches[] = str_replace("//$draftDomain.", '//', $url);
        }

        foreach (static::getAll() as $tenant) {
            if (in_array($tenant->url, $matches, true)) {
                return $tenant;
            }
        }

        return null;
    }

    public static function getFromRequest(): ?Tenant
    {
        $tenantId = Yii::$app->getRequest()->get('tenant', '');
        return static::getAll()[$tenantId] ?? null;
    }

    /**
     * @return array<int, Tenant>
     */
    public static function findAll(): array
    {
        $dependency = new TagDependency(['tags' => static::CACHE_KEY]);

        return Tenant::find()
            ->where(['>', 'status', Tenant::STATUS_DISABLED])
            ->indexBy('id')
            ->orderBy(['position' => SORT_ASC])
            ->cache(0, $dependency)
            ->all();
    }

    public static function invalidateCache(): void
    {
        TagDependency::invalidate(Yii::$app->getCache(), static::CACHE_KEY);
        self::$_tenants = null;
    }
}
