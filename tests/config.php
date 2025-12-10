<?php

declare(strict_types=1);

use Hirtz\Tenant\Bootstrap;

$basePath = (getenv('BASE_PATH') ?: getcwd());
$config = require("$basePath/vendor/davidhirtz/yii2-skeleton/tests/config.php");

return [
    ...$config,
    'bootstrap' => [
        Bootstrap::class,
    ],
];
