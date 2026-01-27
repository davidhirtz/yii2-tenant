<?php

declare(strict_types=1);

namespace davidhirtz\yii2\tenant\modules\admin\widgets\forms;

use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\ModelTimestampTrait;
use davidhirtz\yii2\skeleton\modules\admin\widgets\forms\traits\StatusFieldTrait;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveField;
use davidhirtz\yii2\skeleton\widgets\bootstrap\ActiveForm;
use davidhirtz\yii2\skeleton\widgets\forms\DynamicRangeDropdown;
use davidhirtz\yii2\tenant\models\Tenant;
use Override;
use Yii;

/**
 * @property Tenant $model
 */
class TenantActiveForm extends ActiveForm
{
    use ModelTimestampTrait;
    use StatusFieldTrait;

    /**
     * @see self::languageField()
     */
    #[Override]
    public function init(): void
    {
        $this->fields ??= [
            'status',
            'name',
            'language',
            '-',
            'url',
            'cookie_domain',
        ];

        parent::init();
    }

    /**
     * @see Tenant::getLanguages()
     */
    public function languageField(array $options = []): ActiveField|string
    {
        return !Yii::$app->getUrlManager()->hasI18nUrls()
            ? $this->field($this->model, 'language', $options)->widget(DynamicRangeDropdown::class)
            : '';
    }
}
