<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Forms;

use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\Field;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Hirtz\Skeleton\Widgets\Forms\Fields\SelectField;
use Hirtz\Skeleton\Widgets\Forms\Traits\CustomAttributeFieldsTrait;
use Hirtz\Tenant\Models\Tenant;
use Override;
use Yii;

/**
 * @template T of Tenant
 * @property T $model
 */
class TenantActiveForm extends ActiveForm
{
    use CustomAttributeFieldsTrait;

    #[Override]
    protected function getDefaultRows(): array
    {
        return [
            [
                $this->getStatusField(),
                $this->getNameField(),
                $this->getLanguageField(),
                $this->getLanguagesField(),
                ...$this->getCustomAttributeFields(),
            ],
            [
                $this->getUrlField(),
                $this->getCookieDomainField(),
            ]
        ];
    }

    protected function getStatusField(): ?Field
    {
        return SelectField::make()
            ->property('status');
    }

    protected function getNameField(): ?Field
    {
        return InputField::make()
            ->property('name');
    }

    protected function getLanguageField(): ?Field
    {
        $languages = array_keys(Tenant::getLanguages());

        if (count($languages) === 1) {
            return InputField::make()
                ->property('language')
                ->type('hidden')
                ->value($languages[0]);
        }

        // The default is always available, so the languages field has to lock the new one
        return SelectField::make()
            ->property('language')
            ->prompt($this->getLanguagePrompt())
            ->reloadsForm();
    }

    protected function getLanguagePrompt(): string
    {
        $language = Tenant::getApplicationDefaultLanguage();

        return $language !== null
            ? Yii::t('tenant', 'TENANT_PROMPT_LANGUAGE_DEFAULT', ['language' => Yii::$app->getI18n()->getLabel($language)])
            : Yii::t('tenant', 'TENANT_PROMPT_LANGUAGE');
    }

    protected function getLanguagesField(): ?Field
    {
        $languages = Tenant::getLanguages();

        if (count($languages) < 2) {
            return null;
        }

        $default = $this->model->getDefaultLanguage();

        return SelectField::make()
            ->property('languages')
            ->items($languages)
            ->value($this->model->getAvailableLanguages())
            ->itemAttributes($default !== null ? [$default => ['data-locked' => true]] : [])
            ->multiple();
    }

    protected function getCookieDomainField(): ?Field
    {
        return InputField::make()
            ->property('cookie_domain');
    }

    protected function getUrlField(): ?Field
    {
        return InputField::make()
            ->property('url')
            ->type('url');
    }
}
