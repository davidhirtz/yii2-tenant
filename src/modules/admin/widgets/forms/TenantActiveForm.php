<?php

namespace davidhirtz\yii2\tenant\modules\admin\widgets\forms;

use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;

class TenantActiveForm extends ActiveForm
{
    public function init(): void
    {
        $this->fields ??= [
            'name',
            'language',
            'url',
        ];

        parent::init();
    }

    /**
     * @noinspection PhpUnused
     */
    public function languageField(array $options = []): ActiveField|string
    {
        return $this->field('language', $options)->widget(DynamicRangeDropdown::class);
    }
}
