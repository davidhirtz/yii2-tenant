<?php

namespace PHPSTORM_META {

    override(
        \yii\base\Module::get(0),
        map([
            'tenant' => '\davidhirtz\yii2\tenant\models\Tenant',
        ])
    );
}
