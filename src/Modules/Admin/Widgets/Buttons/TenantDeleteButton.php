<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Buttons;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Widgets\Buttons\DeleteButton;
use Hirtz\Skeleton\Widgets\Traits\ModelTrait;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Override;
use Yii;

/**
 * @see TenantController::actionDelete()
 */
class TenantDeleteButton extends DeleteButton
{
    /**
     * @use ModelTrait<Tenant>
     */
    use ModelTrait;

    public function isVisible(): bool
    {
        return parent::isVisible()
            && $this->model->isDeletable()
            && $this->webuser->can(Tenant::AUTH_TENANT_DELETE, ['tenant' => $this->model]);
    }

    #[Override]
    protected function configure(): void
    {
        $this->property ??= 'name';
        $this->title ??= Lang::t('tenant', 'TENANT_TITLE_DELETE');

        parent::configure();
    }
}
