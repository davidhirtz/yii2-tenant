## 3.0.0 (in development)

- Renamed the namespace `davidhirtz\yii2\tenant` to `Hirtz\Tenant` and the directories to StudlyCase (`Models\Tenant`, `Web\UrlManager`, `Modules\Admin\Controllers\TenantController`); the messages moved to `messages/`, the views to `resources/views/admin/tenant/`
- Merged `davidhirtz/yii2-cms-tenant` into `yii2-cms` and this bundle: `Filters\PageCache` and the sitemap `variations` callback live here, everything entry-related in the cms, which now requires this bundle
- Removed the `tenant` application component; the current tenant is `Web\UrlManager::$tenant`, `null` under a console application
- Replaced `Tenant::AUTH_TENANT_CREATE`, `AUTH_TENANT_UPDATE` and `AUTH_TENANT_DELETE` with the single permission `Tenant::AUTH_TENANT` (`tenant`), described by `AUTH_TENANT_DESCRIPTION` and held by `admin` alone; `findTenant()` lost its permission argument
- Removed `Modules\Admin\Widgets\Navs\TenantSubmenu`; added `TenantHeader`, `TenantActionDropdown`, `TenantNavItem` and `Buttons\TenantDeleteButton`, and moved the admin routes from `admin/tenant/<action>` to `admin/tenant/tenant/<action>`
- Renamed the message keys `TENANT_FLASH_*` to `TENANT_SUCCESS_*` and `TENANT_TITLE_CREATE` to `TENANT_CREATE_TITLE`; removed `TENANT_AUTH_*`, `TENANT_TITLE_UPDATE`, `TENANT_TITLE_DELETE` and the `ru`, `zh-CN` and `zh-TW` message files
- Renamed `Web\UrlManager::setApplicationLanguage()` to `setLanguage()`; `TenantCollection::getDefault()` answers `null` instead of throwing, `Tenant::getLanguages()` a plain `code => label` map
- Changed `tenant.url` and `tenant.language` to nullable: a tenant without a URL pins no host and no cookie domain (`getHostInfo()` and `getCookieDomain()` answer `null`), one without a language keeps the configured `defaultLanguage`; the seeded tenant (`Migrations\M260101000110TenantSeed`) has neither
- Changed the cookie domain to be resolved per request host through `TenantCollection::getCookieDomainByHostInfo()` rather than from the matched tenant
- Changed `Web\UrlManager::parseRequest()` to keep the tenant's canonical host as `hostInfo` after the parse, and `createUrl()` to work without a web request
- Changed `Tenant` to implement the skeleton's `AdminModelInterface`, `TrailModelInterface` and `CustomAttributeInterface`: `getTrailModelType()`, `getTrailModelName()` and `getTrailModelAdminRoute()` are `getAdminType()`, `getAdminName()` and `getAdminRoute()`; added the `custom_attributes` column
- Changed `TenantRelationTrait` to declare no `tenant_id`; the using model declares the column. `TenantQueryTrait::andWhereTenant()` prefixes the column with the table alias
- Changed `TenantActiveForm` to declare its fields in `getDefaultRows()`, and `TenantController::actionOrder()` to answer a flash fragment (`TENANT_SUCCESS_ORDERED`)
- Added `Module` (`modules.tenant`) with `$enableAdminModule`, and `Modules\ModuleTrait::getModule()`
- Added `TenantCollection::getById()`, `TenantController::actionStatus()` behind the grid's `enableStatusUpdate`, and `Registry\Report`, which gives the registry report the tenants' URLs
- Added `Test\TestCase`, `Test\Fixtures\TenantFixture` and `Test\Traits\TenantFixtureTrait`

## 1.4.0 (Jan 27, 2026)

- Enhanced `UrlManager` to support I18N URLs with tenant domains

## 1.3.3 (Dec 17, 2025)

- Fixed `UrlManager::createUrl` with string parameters and draft domains

## 1.3.2 (Dec 6, 2025)

- Fixed redirect URL after deleting a tenant

## 1.3.1 (Oct 20, 2025)

- Added Russian language support

## 1.3.0 (May 25, 2025)

- Changed the required PHP version to 8.3
- Added `TenantCollection::getDefault()`
- Updated `UrlManager::$draftDomain`

## 1.2.4 (Apr 3, 2025)

- Invalidate cache in `Tenant::afterDelete()` (Issue #4)

## 1.2.3 (Feb 10, 2025)

- Fixed missing path info for draft URLs

## 1.2.2 (Feb 10, 2025)

- Enhanced `TenantCollection::getVisibleTenants()`

## 1.2.1 (Feb 10, 2025)

- Added `TenantCollection::getVisibleTenants()`

## 1.2.0 (Feb 10, 2025)

- Added tenant draft status, which allows tenants to be edited without being visible to robots

## 1.1.8 (Jan 23, 2025)

- Changed `Bootstrap` I18N configuration
- Added PHPStan V2

## 1.1.7 (Dec 23, 2024)

- Added `language` to the required attributes in `Tenant`

## 1.1.6 (Nov 29, 2024)

- Fixed `DynamicRangeValidator` for tenant language

## 1.1.5 (Oct 8, 2024)

- Fixed migrations for previous installations

## 1.1.4 (Oct 2, 2024)

- Fixed `UrlManager` to update path info only after tenant was found via request
- Improved `TenantCollection::getByUrl()` with draft domains

## 1.1.3 (Sep 24, 2024)

- Added `Tenant::$position` attribute to allow sorting tenants (Issue #2)
- Enhanced `Trail::getTrailAttributes()` to include custom fields (Issue #1)

## 1.1.2 (Sep 18, 2024)

- Reverted the default tenant creation from the `UrlManager` component to the migration, setting the cookie domain only
  if the tenant was found fixes the issue of the cookie being set on the wrong domain

## 1.1.1 (Sep 18, 2024)

- Moved the default tenant creation from the migration to the `UrlManager` component to allow the tenant to be created
  on the first request with the correct host info
- Enhanced `UrlManager` to set a temporary cookie if the default tenant needed to be selected

## 1.1.0 (Sep 8, 2024)

- Added `Tenant::$cookie_domain` attribute

## 1.0.1 (Aug 28, 2024)

- Added `TenantRelationTrait` and `TenantQueryTrait`