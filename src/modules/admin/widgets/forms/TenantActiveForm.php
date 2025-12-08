<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Forms;

use Hirtz\Skeleton\Widgets\Forms\ActiveForm;
use Hirtz\Skeleton\Widgets\Forms\Fields\Field;
use Hirtz\Skeleton\Widgets\Forms\Fields\InputField;
use Hirtz\Skeleton\Widgets\Forms\Fields\SelectField;
use Hirtz\Tenant\models\Tenant;

/**
 * @template T of Tenant
 * @property T $model
 */
class TenantActiveForm extends ActiveForm
{
    protected function configure(): void
    {
        $this->rows ??= [
            [
                $this->getStatusField(),
                $this->getNameField(),
                $this->getLanguageField(),
            ],
            [
                $this->getUrlField(),
                $this->getCookieDomainField(),
            ]
        ];

        parent::configure();
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
        return SelectField::make()
            ->property('language');
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
