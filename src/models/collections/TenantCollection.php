<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\models\collections;

use davidhirtz\yii2\tenant\models\Tenant;
use Yii;
use yii\base\InvalidConfigException;
use yii\caching\TagDependency;

class TenantCollection
{
    public const CACHE_KEY = 'tenant-collection';

    protected static ?array $_tenants = null;

    /**
     * @return array<int, Tenant>
     */
    public static function getAll(): array
    {
        return static::$_tenants ??= static::findAll();
    }

    public static function getDefault(): Tenant
    {
        $tenants = static::getAll();

        if (!$tenants) {
            throw new InvalidConfigException('No tenants found.');
        }

        return reset($tenants);
    }

    /**
     * @return array<int, Tenant>
     */
    public static function getVisibleTenants(): array
    {
        $tenant = Yii::$app->get('tenant');
        return array_filter(static::getAll(), fn (Tenant $current) => $current->status >= $tenant->status);
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
            if (in_array($tenant->url, $matches)) {
                return $tenant;
            }
        }

        return null;
    }

    public static function getFromRequest(): ?Tenant
    {
        $tenantId = Yii::$app->getRequest()->get('tenant');
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
