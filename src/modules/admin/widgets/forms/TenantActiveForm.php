<?php

namespace davidhirtz\yii2\tenant\modules\admin\widgets\forms;

use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;
use davidhirtz\yii2\tenant\models\Tenant;

/**
 * @property Tenant $model
 */
class TenantActiveForm extends ActiveForm
{
    use ModelTimestampTrait;
    
    public function init(): void
    {
        $this->fields ??= [
            'name',
            'url',
            'language',
        ];

        parent::init();
    }

    /**
     * @see          Tenant::getLanguages()
     * @noinspection PhpUnused
     */
    public function languageField(array $options = []): ActiveField|string
    {
        return $this->field($this->model, 'language', $options)->widget(DynamicRangeDropdown::class);
    }
}
