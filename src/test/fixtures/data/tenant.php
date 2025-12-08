<?php

declare(strict_types=1);

use Hirtz\Tenant\models\Tenant;
use yii\db\Expression;

return [
    'default' => [
        'id' => 1,
        'status' => Tenant::STATUS_ENABLED,
        'name' => 'Default Tenant',
        'url' => 'https://www.domain.com',
        'cookie_domain' => '.domain.com',
        'language' => 'en-US',
        'position' => 1,
        'created_at' => new Expression('UTC_TIMESTAMP()'),
    ],
    'path' => [
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
