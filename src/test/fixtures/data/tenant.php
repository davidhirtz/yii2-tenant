<?php

declare(strict_types=1);

use Hirtz\Tenant\Models\Tenant;
use yii\db\Expression;

return [
    'default' => [
        'id' => 1,
        'status' => Tenant::STATUS_ENABLED,
        'name' => 'Default',
        'url' => 'https://www.domain.localhost',
        'language' => 'en-US',
        'position' => 1,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'enabled' => [
        'id' => 2,
        'status' => Tenant::STATUS_ENABLED,
        'name' => 'German Tenant',
        'url' => 'https://www.domain.localhost/de',
        'cookie_domain' => '.domain.localhost',
        'language' => 'de',
        'position' => 2,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'draft' => [
        'id' => 3,
        'status' => Tenant::STATUS_DRAFT,
        'name' => 'Draft Tenant',
        'url' => 'https://www.draft.localhost',
        'language' => 'en-US',
        'position' => 3,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
