<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Hirtz\Skeleton\Models\Interfaces\StatusAttributeInterface;
use Hirtz\Tenant\Models\Tenant;
use Yii;
use yii\base\InvalidConfigException;
use yii\db\Expression;
use yii\db\Migration;

/**
 * Seeds the tenant every database must have.
 *
 * Separate from the baseline because it cannot be static SQL: the URL comes from the environment. And separate
 * from the v2 → v3 migrations, which `v3-migration-squash.md` §4 moved to `davidhirtz/yii2-upgrade`, because
 * this is not a conversion — a fresh install needs a tenant as much as an upgraded one does.
 *
 * Idempotent, so it is right on both paths: a database that already has a tenant, whether from the baseline's
 * successor or from its own v2 history, is left alone.
 *
 * @noinspection PhpUnused
 */
class M260101000110TenantSeed extends Migration
{
    use MigrationTrait;

    public function safeUp(): void
    {
        if ($this->getDb()->createCommand('SELECT COUNT(*) FROM ' . $this->getQuotedTableName(Tenant::tableName()))->queryScalar()) {
            return;
        }

        $url = $this->getDefaultTenantUrl();

        $this->insert(Tenant::tableName(), [
            'status' => StatusAttributeInterface::STATUS_ENABLED,
            'name' => ($url ? parse_url($url, PHP_URL_HOST) : null) ?: Yii::$app->name,
            'url' => $url,
            'language' => null,
            'position' => 1,
            'created_at' => new Expression('UTC_TIMESTAMP()'),
        ]);
    }

    public function safeDown(): bool
    {
        echo "    > the seeded tenant is not removed, an installation without one cannot serve\n";

        return false;
    }

    /**
     * The canonical host of the site, per environment: it becomes the URL manager's `hostInfo` on every
     * request. Never guessed — an installation that names none gets a tenant without a URL, which pins no host
     * at all and so follows the request, which is what a site that never wanted tenants runs on.
     */
    protected function getDefaultTenantUrl(): ?string
    {
        $url = $this->getHostInfo() ?: (Yii::$app->params['tenantUrl'] ?? null);

        return is_string($url) && $url ? rtrim($url, '/') : null;
    }

    protected function getHostInfo(): ?string
    {
        try {
            return Yii::$app->getUrlManager()->getHostInfo() ?: null;
        } catch (InvalidConfigException) {
            return null;
        }
    }
}
