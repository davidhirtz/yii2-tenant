<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Hirtz\Skeleton\Db\Traits\MigrationTrait;
use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M261029100000MessageKeys extends Migration
{
    use MigrationTrait;

    private const array KEYS = [
        'TENANT_TRAIL_REORDERED' => 'TENANT_ORDER_TRAIL',
    ];

    #[Override]
    public function safeUp(): void
    {
        $this->renameMessageKeys('tenant', self::KEYS);
    }

    #[Override]
    public function safeDown(): void
    {
        $this->renameMessageKeys('tenant', array_flip(self::KEYS));
    }
}
