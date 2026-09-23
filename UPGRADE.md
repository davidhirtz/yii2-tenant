# Upgrading to 3.0

The previous major of this bundle is 1.x (branch `v1`). Every 1.x installation also ran the glue package
`davidhirtz/yii2-cms-tenant`, which 3.0 folds into `yii2-cms` and this bundle.

## Requirements

- PHP `^8.3`
- `davidhirtz/yii2-skeleton` `^3.0`; `davidhirtz/yii2-cms` `^3.0` requires this bundle itself
- Remove `davidhirtz/yii2-cms-tenant` from `composer.json`: the package no longer exists

## Renames

Namespaces (directories are StudlyCase now):

| 1.x                                                  | 3.0                                                    |
|------------------------------------------------------|--------------------------------------------------------|
| `davidhirtz\yii2\tenant\`                            | `Hirtz\Tenant\`                                        |
| `davidhirtz\yii2\tenant\models\`                     | `Hirtz\Tenant\Models\`                                 |
| `davidhirtz\yii2\tenant\models\actions\`             | `Hirtz\Tenant\Models\Actions\`                         |
| `davidhirtz\yii2\tenant\models\collections\`         | `Hirtz\Tenant\Models\Collections\`                     |
| `davidhirtz\yii2\tenant\models\queries\`             | `Hirtz\Tenant\Models\Queries\`                         |
| `davidhirtz\yii2\tenant\models\queries\traits\`      | `Hirtz\Tenant\Models\Queries\Traits\`                  |
| `davidhirtz\yii2\tenant\models\traits\`              | `Hirtz\Tenant\Models\Traits\`                          |
| `davidhirtz\yii2\tenant\modules\admin\`              | `Hirtz\Tenant\Modules\Admin\`                          |
| `davidhirtz\yii2\tenant\modules\admin\controllers\`  | `Hirtz\Tenant\Modules\Admin\Controllers\`              |
| `davidhirtz\yii2\tenant\modules\admin\data\`         | `Hirtz\Tenant\Modules\Admin\Data\`                     |
| `davidhirtz\yii2\tenant\modules\admin\widgets\`      | `Hirtz\Tenant\Modules\Admin\Widgets\`                  |
| `davidhirtz\yii2\tenant\web\`                        | `Hirtz\Tenant\Web\`                                    |
| `davidhirtz\yii2\tenant\migrations\`                 | `Hirtz\Tenant\Migrations\`                             |
| `davidhirtz\yii2\tenant\src\messages\`               | `messages/` at the bundle root                         |
| `davidhirtz\yii2\tenant\modules\admin\views\tenant\` | `resources/views/admin/tenant/`                        |

Classes of `davidhirtz/yii2-cms-tenant`:

| 1.x                                                             | 3.0                                                            |
|-----------------------------------------------------------------|----------------------------------------------------------------|
| `davidhirtz\yii2\cms\tenant\filters\PageCache`                  | `Hirtz\Tenant\Filters\PageCache`                               |
| `davidhirtz\yii2\cms\tenant\models\Entry`                       | `Hirtz\Cms\Models\Entry`                                       |
| `davidhirtz\yii2\cms\tenant\models\queries\EntryQuery`          | `Hirtz\Cms\Models\Queries\EntryQuery`                          |
| `davidhirtz\yii2\cms\tenant\data\EntryActiveDataProvider`       | `Hirtz\Cms\Modules\Admin\Data\EntryActiveDataProvider`         |
| `davidhirtz\yii2\cms\tenant\validators\TenantIdValidator`       | `Hirtz\Cms\Validators\TenantIdValidator`                       |
| `davidhirtz\yii2\cms\tenant\widgets\grids\EntryGridView`        | `Hirtz\Cms\Modules\Admin\Widgets\Grids\EntryGridView`          |
| `davidhirtz\yii2\cms\tenant\widgets\grids\SectionParentEntryGridView` | `Hirtz\Cms\Modules\Admin\Widgets\Grids\SectionParentEntryGridView` |
| `davidhirtz\yii2\cms\tenant\widgets\grids\TenantGridView`       | `Hirtz\Cms\Modules\Admin\Widgets\Grids\TenantGridView`         |
| `davidhirtz\yii2\cms\tenant\Bootstrap`                          | removed; `Hirtz\Cms\Bootstrap` and `Hirtz\Tenant\Bootstrap`    |
| `davidhirtz\yii2\cms\tenant\behaviors\EntryTenantBehavior`      | removed; `Hirtz\Cms\Models\Entry` owns the tenant itself       |
| `davidhirtz\yii2\cms\tenant\behaviors\TenantEntryBehavior`      | removed; `Hirtz\Cms\Models\Events\TenantBeforeDeleteEventHandler`, `TenantAfterSaveEventHandler` |
| `davidhirtz\yii2\cms\tenant\widgets\forms\TenantIdFieldBehavior` | removed; `Hirtz\Cms\Modules\Admin\Widgets\Forms\Fields\TenantIdField` |
| `davidhirtz\yii2\cms\tenant\widgets\forms\EntryParentIdDropDown` | removed                                                       |
| `davidhirtz\yii2\cms\tenant\widgets\grids\traits\EntryCountColumnTrait`, `EntryGridViewTrait`, `TenantDropdownTrait` | removed |
| `davidhirtz\yii2\cms\tenant\assets\AssetBundle`                 | removed; the tenant select reloads the form, no script          |
| `davidhirtz\yii2\cms\tenant\migrations\M240819124325CmsTenant`  | removed; see *Data and schema*                                 |

Classes of this bundle:

| 1.x                                                                 | 3.0                                                                                        |
|---------------------------------------------------------------------|--------------------------------------------------------------------------------------------|
| `modules\admin\widgets\navs\TenantSubmenu`                          | removed; `Modules\Admin\Widgets\Navs\TenantHeader` renders title, breadcrumbs and actions  |
| `modules\admin\Module`                                              | `Modules\Admin\Module` (the admin submodule); new `Module` is the application module `tenant` |
| `migrations\M240819124324Tenant`, `M240828161128CookieDomain`, `M240905060625Roles`, `M240924195507TenantPosition`, `M250210134700Draft` | `Migrations\M260101000100TenantBaseline`, `M260101000110TenantSeed` (fresh installs) |
| —                                                                   | `Modules\ModuleTrait`, `Modules\Admin\Widgets\Navs\TenantActionDropdown`, `TenantNavItem`, `Modules\Admin\Widgets\Buttons\TenantDeleteButton`, `Registry\Report` |
| —                                                                   | `Test\TestCase`, `Test\Fixtures\TenantFixture`, `Test\Traits\TenantFixtureTrait`           |

Methods and properties:

| 1.x                                                         | 3.0                                                                          |
|-------------------------------------------------------------|------------------------------------------------------------------------------|
| `Yii::$app->get('tenant')` (the `tenant` component)         | `Web\UrlManager::$tenant`                                                    |
| `web\UrlManager::setApplicationLanguage()`                  | `Web\UrlManager::setLanguage()`                                              |
| `web\UrlManager::getTenantFromRequest()` (protected, `Tenant`) | public, `?Tenant`                                                         |
| `Tenant::getTrailModelType()`                               | `Tenant::getAdminType()`                                                     |
| `Tenant::getTrailModelName()`                               | `Tenant::getAdminName()` (`AdminModelTrait`)                                 |
| `Tenant::getTrailModelAdminRoute()`                         | `Tenant::getAdminRoute()`                                                    |
| `Tenant::getHostInfo(): string`                             | `?string`                                                                    |
| `Tenant::getCookieDomain(): string`                         | `?string`                                                                    |
| `Tenant::getLanguages()`, `code => ['name' => label]`       | `code => label`                                                              |
| `TenantCollection::getDefault(): Tenant` (throws)           | `?Tenant`                                                                    |
| `TenantCollection::getAll()[$id] ?? null`                   | `TenantCollection::getById(?int $id)`                                        |
| `TenantControllerTrait::findTenant(int $id, ?string $permission)` | `findTenant(int $id)`                                                  |
| `TenantController::actionOrder(): void`                     | `: string`, a flash fragment                                                 |
| `TenantActiveForm::init()` assigning `$this->rows`          | `TenantActiveForm::getDefaultRows()`                                         |
| `TenantRelationTrait` declaring `@property int|null $tenant_id` | the using model declares the column                                      |
| —                                                           | `TenantCollection::getCookieDomainByHostInfo()`, `reset()`, `TenantController::actionStatus()` |

Constants:

| 1.x                                         | 3.0                              |
|---------------------------------------------|----------------------------------|
| `Tenant::AUTH_TENANT_CREATE` (`tenantCreate`) | `Tenant::AUTH_TENANT` (`tenant`) |
| `Tenant::AUTH_TENANT_UPDATE` (`tenantUpdate`) | `Tenant::AUTH_TENANT`            |
| `Tenant::AUTH_TENANT_DELETE` (`tenantDelete`) | `Tenant::AUTH_TENANT`            |

Config keys:

| 1.x                                       | 3.0                                              |
|-------------------------------------------|--------------------------------------------------|
| `components.tenant` (set at runtime only) | gone                                             |
| —                                         | `modules.tenant.enableAdminModule` (`true`)      |

Columns:

| 1.x                                                  | 3.0                                                              |
|------------------------------------------------------|------------------------------------------------------------------|
| `tenant.url` `NOT NULL`                              | nullable, still unique                                           |
| `tenant.language` `NOT NULL`                         | nullable                                                         |
| —                                                    | `tenant.custom_attributes` (JSON)                                |
| —                                                    | `tenant.entry_count` (owned by `yii2-cms`)                       |
| `entry.tenant_id` nullable (`yii2-cms-tenant`)       | `NOT NULL`, foreign key `ON DELETE CASCADE` (`yii2-cms`)         |
| `permalink.model_class`, `model_id`                  | `permalink.entry_id`, `permalink.tenant_id` (`yii2-cms`)         |

Message keys (category `tenant`):

| 1.x                                                             | 3.0                                                              |
|-----------------------------------------------------------------|------------------------------------------------------------------|
| `TENANT_AUTH_CREATE`, `TENANT_AUTH_UPDATE`, `TENANT_AUTH_DELETE` | `AUTH_TENANT_DESCRIPTION`                                       |
| `TENANT_FLASH_CREATED`, `TENANT_FLASH_UPDATED`, `TENANT_FLASH_DELETED` | `TENANT_SUCCESS_CREATED`, `TENANT_SUCCESS_UPDATED`, `TENANT_SUCCESS_DELETED` |
| `TENANT_TITLE_CREATE`                                           | `TENANT_CREATE_TITLE`                                            |
| `TENANT_TITLE_UPDATE`, `TENANT_TITLE_DELETE`                    | removed; the header shows the tenant's name, the delete button carries `TENANT_BUTTON_DELETE` and `TENANT_CONFIRM_DELETE` |
| —                                                               | `TENANT_HINT_URL`, `TENANT_PROMPT_LANGUAGE`, `TENANT_SUCCESS_ORDERED` |
| `ru`, `zh-CN`, `zh-TW` message files                            | removed; `de`, `en-US`, `fr`, `pt` ship                          |

Admin routes:

| 1.x                     | 3.0                                                       |
|-------------------------|-----------------------------------------------------------|
| `admin/tenant/index`    | `admin/tenant/tenant/index` (`admin/tenant` still resolves) |
| `admin/tenant/create`   | `admin/tenant/tenant/create`                              |
| `admin/tenant/update`   | `admin/tenant/tenant/update`                              |
| `admin/tenant/delete`   | `admin/tenant/tenant/delete`                              |
| `admin/tenant/order`    | `admin/tenant/tenant/order`                               |
| —                       | `admin/tenant/tenant/status` (POST)                       |

## Configuration

Drop `davidhirtz/yii2-cms-tenant` from `composer.json` and any container definition or `bootstrap` entry naming
a `davidhirtz\yii2\cms\tenant\` class; `Hirtz\Cms\Bootstrap` and `Hirtz\Tenant\Bootstrap` register what that
package's bootstrap used to. A project that bound `davidhirtz\yii2\tenant\web\UrlManager` (or a subclass) in the
container renames the class; the bootstrap only sets the definition when none exists, as before.

An installation that has one tenant and wants no tenant admin turns the admin submodule off; the tenant row stays
and is editable through the console or SQL only:

```php
// before: nothing, the admin pages were always registered
// after
'modules' => [
    'tenant' => [
        'enableAdminModule' => false,
    ],
],
```

Custom attributes on the tenant are new and optional:

```php
'container' => [
    'definitions' => [
        \Hirtz\Tenant\Models\Tenant::class => [
            'customAttributes' => fn (): array => [
                \Hirtz\Skeleton\Models\CustomAttributes\TextCustomAttribute::make('contact_email'),
            ],
        ],
    ],
],
```

## Code changes

### The current tenant is on the URL manager

1.x kept the resolved tenant as the application component `tenant`; 3.0 keeps it on the URL manager, which is
also where a console application, having parsed no request, has none.

```php
// before
$tenant = Yii::$app->get('tenant');

// after
$manager = Yii::$app->getUrlManager();
$tenant = $manager instanceof \Hirtz\Tenant\Web\UrlManager ? $manager->tenant : null;
```

`TenantQueryTrait::andWhereCurrentTenant()` is a no-op without a current tenant instead of failing.

### The tenant's language is the default language, not the application language

1.x assigned `Yii::$app->language` from the tenant on every request. 3.0 assigns the tenant's language to the URL
manager's `defaultLanguage` and lets the skeleton resolve the request language from there, so `i18nUrl` prefixes
and the browser's preferred language work per tenant. A tenant without a language (the column is nullable now)
keeps whatever `defaultLanguage` the project configured. Code that read the tenant to learn the language reads
`Yii::$app->language`.

### `url`, host and cookie domain are optional

`Tenant::getHostInfo()` and `getCookieDomain()` answer `null` for a tenant without a URL, `getPathInfo()` an empty
string, and `TenantCollection::getDefault()` answers `null` on an empty table instead of throwing. A caller that
concatenated `getHostInfo()` into a URL guards the `null`; `Tenant::getAbsoluteUrl()` falls back to the request's
host. The cookie domain is resolved per request host (`TenantCollection::getCookieDomainByHostInfo()`) rather
than from the tenant that matched, so a path tenant no longer writes cookies under a scope of its own.

### One permission

`tenantCreate`, `tenantUpdate` and `tenantDelete` are one permission, `tenant`, and no check takes a record.

```php
// before
Yii::$app->getUser()->can(Tenant::AUTH_TENANT_UPDATE, ['tenant' => $tenant]);
'roles' => [Tenant::AUTH_TENANT_CREATE, Tenant::AUTH_TENANT_UPDATE],

// after
$this->webuser->can(Tenant::AUTH_TENANT);
'roles' => [Tenant::AUTH_TENANT],
```

`manager` does not hold `tenant`; grant it explicitly where a manager account edited tenants.

### Admin widgets

The Yii widget API is gone. `TenantSubmenu::widget(['model' => $tenant])` becomes
`TenantHeader::make()->model($tenant)` (or `->provider($provider)` on the index, `->title(...)` on the create
page); `TenantGridView::widget(['dataProvider' => $provider])` becomes `TenantGridView::make()->provider($provider)`
inside a `GridContainer`; `TenantActiveForm::widget(['model' => $tenant])` becomes
`TenantActiveForm::make()->model($tenant)` inside a `FormContainer`. The delete form is a `TenantDeleteButton` in
the header's `TenantActionDropdown`, not a panel in the view. A subclass of `TenantActiveForm` that assigned
`$this->rows` in `init()` declares its fields in `getDefaultRows()`; a subclass of the grid overrides the
`get*Column()` methods. The nav item is `TenantNavItem`, replacing the admin module's `getNavBarItems()`.

### Trail and admin model interface

`Tenant` implements the skeleton's `AdminModelInterface` (through `TrailModelInterface`): `getTrailModelType()`
is `getAdminType()`, `getTrailModelName()` is `getAdminName()`, `getTrailModelAdminRoute()` is `getAdminRoute()`,
and `getPermissionName()` answers `tenant`. `getAdminRoute()` answers the index route for a new record.

### `TenantRelationTrait` declares no `tenant_id`

The trait keeps `getTenant()` and `populateTenantRelation()` but no longer declares the `@property` for the
foreign key. A model using it declares `@property int $tenant_id` (or `int|null`) itself.

### cms projects

A project `Entry` extends `Hirtz\Cms\Models\Entry` again; the cms-tenant `Entry`, `EntryQuery` and the two
behaviors are gone. `entry.tenant_id` is required and validated by `Hirtz\Cms\Validators\TenantIdValidator`,
which resolves an empty value to `TenantCollection::getDefault()`, so imports and single-tenant code need not name
a tenant. Permalinks carry the entry's `tenant_id` in their unique index, and an entry's redirects are recorded
host-qualified. `yii2-cms/UPGRADE.md` covers the entry side.

### Console

`Web\UrlManager::createUrl()` no longer reads the current tenant off a web request, so a console command builds
tenant URLs without failing; an absolute URL still needs a tenant with a URL or a configured `urlManager.hostInfo`.
`./yii registry/push` reports the first tenant URL as the installation's `url` (`Registry\Report`).

## Data and schema

The 1.x migration history is retired: the upgrade tool records `Migrations\M260101000100TenantBaseline` as applied
in place of the `davidhirtz\yii2\tenant\migrations\` rows, and only a fresh install runs the baseline and
`M260101000110TenantSeed`. The upgrade itself is the migrations in `davidhirtz/yii2-upgrade`,
`migrations/yii2-tenant/`, interleaved by timestamp with the skeleton's and the cms's. Back the database up
first. In order:

1. `M260904093000NullableLanguage` makes `tenant.language` nullable.
2. `M260907110000NullableUrl` makes `tenant.url` nullable. It is named to run before the cms
   `M260908100000Tenant`, which seeds a tenant into an empty table (reading the console `urlManager.hostInfo`,
   then `params['tenantUrl']`, else no URL), makes `entry.tenant_id` `NOT NULL` with a foreign key and adds
   `tenant.entry_count`. An upgraded 1.x database already has its tenants and only gains what is missing; an
   entry without a tenant is assigned the single tenant, and with several tenants the migration stops and asks
   for a repair by hand. `M260909100000Permalink` then builds `permalink` with the entry's `tenant_id`.
3. `M260911120000CustomAttributes` adds `tenant.custom_attributes`.
4. `M260914140000AuthItems` adds the `tenant` permission under `admin` and replaces `tenantCreate`,
   `tenantUpdate` and `tenantDelete` with it wherever a role or user held one
   (`MigrationTrait::replaceAuthItems()`).
5. `M260915170000CustomAttributesColumn` moves `custom_attributes` after `language`; column order only.
6. `M260915190000ManagerTenantPermission` takes `tenant` out of the `manager` role.

Afterwards nothing tenant-specific is needed; the auth cache is invalidated by the migrations, and the tenant
collection is rebuilt on the next request. What is lost: the three-way permission split, so an account that held
only `tenantUpdate` can now create and delete tenants, and a manager account loses tenant access until it is
granted `tenant`. Nothing in the `tenant` table is lost; `params['tenantUrl']` is read by that one cms migration
and by nothing after it.

## Removed

- The `tenant` application component; the tenant lives on `Web\UrlManager::$tenant`.
- `Modules\Admin\Widgets\Navs\TenantSubmenu`; the header, action dropdown and nav item replace it.
- `Tenant::AUTH_TENANT_CREATE`, `AUTH_TENANT_UPDATE`, `AUTH_TENANT_DELETE` and the per-record permission check.
- `davidhirtz/yii2-cms-tenant` as a package, with `EntryTenantBehavior`, `TenantEntryBehavior`,
  `TenantIdFieldBehavior`, `EntryParentIdDropDown`, the three grid traits and its asset bundle.
- The `ru`, `zh-CN` and `zh-TW` translations.
- The `Default` tenant at `https://www.example.com/` the first 1.x migration inserted; a fresh 3.0 install seeds a
  tenant named after the application with no URL.
