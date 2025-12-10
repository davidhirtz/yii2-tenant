<?php

declare(strict_types=1);

use Hirtz\Tenant\Models\Tenant;
use yii\db\Expression;

return [
    'enabled' => [
        'id' => 2,
        'status' => Tenant::STATUS_ENABLED,
        'name' => 'German Tenant',
        'url' => 'https://www.domain.com/de',
        'cookie_domain' => '.domain.com',
        'language' => 'de',
        'position' => 2,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'draft' => [
        'id' => 3,
        'status' => Tenant::STATUS_DRAFT,
        'name' => 'Draft Tenant',
        'url' => 'https://www.example.com',
        'language' => 'en-US',
        'position' => 3,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
];
