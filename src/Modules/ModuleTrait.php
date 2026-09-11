<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules;

use Hirtz\Tenant\Module;
use Yii;

trait ModuleTrait
{
    public static function getModule(): Module
    {
        /** @var Module $module */
        $module = Yii::$app->getModule('tenant');
        return $module;
    }
}
