<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Widgets\Buttons;

use Closure;
use Hirtz\Skeleton\Helpers\EventHelper;
use Hirtz\Skeleton\Html\Div;
use Hirtz\Skeleton\Html\Span;
use Hirtz\Skeleton\Widgets\Buttons\Button;
use Hirtz\Skeleton\Widgets\Icon;
use Hirtz\Skeleton\Widgets\Navs\Dropdown;
use Hirtz\Skeleton\Widgets\Navs\DropdownOptionLink;
use Hirtz\Skeleton\Widgets\Widget;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Module;
use Override;
use Stringable;
use Yii;

class TenantDropdownButton extends Widget
{
    /**
     * A `Closure` keeps a bootstrap from autoloading the models the permission constants live on.
     *
     * @param list<string>|Closure(): list<string> $roles
     */
    public static function addRoles(array|Closure $roles): void
    {
        EventHelper::on(static::class, Widget::EVENT_CONFIGURE, function (self $widget) use ($roles): void {
            $widget->roles($roles instanceof Closure ? $roles() : $roles);
        });
    }

    /**
     * A project overrides it to shorten a long name; the dropdown lists the full ones.
     */
    public function getButtonText(Tenant $tenant): string
    {
        return $tenant->name;
    }

    #[Override]
    protected function configure(): void
    {
        $this->roles ??= [Tenant::AUTH_TENANT];
        parent::configure();
    }

    #[Override]
    protected function renderContent(): string|Stringable
    {
        $tenants = TenantCollection::getAll();
        $current = Module::getCurrentTenant();

        if (!$current || count($tenants) < 2) {
            return '';
        }

        // A phone has no room for a name beside the search and the language: the icon stands in for it.
        $button = Button::make()
            ->class('btn btn-border')
            ->content(Div::make()
                ->class('icon-text')
                ->content(
                    Icon::make()->name('window-restore')->addClass('md:hidden'),
                    Span::make()->class('hidden md:block')->text($this->getButtonText($current)),
                ))
            ->attribute('aria-label', Yii::t('tenant', 'TENANT_SWITCH_TOOLTIP'));

        $dropdown = Dropdown::make()
            ->button($button)
            ->popover(fn (Div $tag) => $tag->attribute('id', 'tenants'));

        foreach ($tenants as $tenant) {
            $dropdown->addItem(DropdownOptionLink::make()
                ->text($tenant->name)
                ->href(Module::getSwitchUrl($tenant))
                ->addClass($tenant->id === $current->id ? 'selected' : null)
                // The navbar outside `#wrap` shows the tenant's languages.
                ->attribute('hx-boost', 'false'));
        }

        return $dropdown;
    }
}
