<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Buttons;

use Hirtz\Skeleton\Widgets\Buttons\DeleteButton;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\TenantController;
use Override;
use Yii;

/**
 * @see TenantController::actionDelete()
 *
 * @extends DeleteButton<Tenant>
 */
class TenantDeleteButton extends DeleteButton
{
    #[\Override]
    public function isVisible(): bool
    {
        return parent::isVisible()
            && $this->model->isDeletable()
            && $this->webuser->can(Tenant::AUTH_TENANT);
    }

    #[Override]
    protected function configure(): void
    {
        $this->property ??= 'name';
        $this->label ??= Yii::t('tenant', 'TENANT_BUTTON_DELETE');
        $this->title ??= Yii::t('tenant', 'TENANT_CONFIRM_DELETE');

        parent::configure();
    }
}
