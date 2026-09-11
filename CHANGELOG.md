## 3.0.0 (in development)

- Added `Module`, registered as the application module `tenant`, with `$enableAdminModule`. Set it to `false`
  and the admin module and its dashboard roles are not registered at all, so the routes 404 instead of a hidden
  nav item covering a live controller. Added `Modules\ModuleTrait` with the static `getModule()`
- `Filters\PageCache` moved here from `yii2-cms-tenant` and `Bootstrap` maps the skeleton `Filters\PageCache`
  to it, the way it maps the URL manager. The sitemap `variations` callback is registered here too
- `Models\Queries\Traits\TenantQueryTrait::andWhereTenant()` prefixes `tenant_id` with the query's table alias,
  so it survives a join against another table that has the column
- `Test\Fixtures\TenantFixture` clears the table before it loads: every migrated database now carries a seeded
  tenant row, and it invalidates the collection cache, which is static and outlives a test's application

- `Models\Tenant` implements the skeleton `Models\Interfaces\AdminRouteInterface` and dropped its
  `getTrailModelAdminRoute()`
- `Models\Tenant` implements `CustomAttributeInterface`. Added the `custom_attributes` column to `tenant`, excluded
  from the trail. `Tenant` has no `type`, so a project declares its definitions by overriding `getCustomAttributes()`
- `TenantActiveForm` renders the custom attribute fields and `TenantController` guards its save with
  `Request::isFormReload()`

- `TenantController::actionOrder()` now returns a flash fragment (was `void`) and emits a success flash
  after a reorder; added the `TENANT_SUCCESS_ORDERED` message
- Changed `Tenant::$language` to be nullable; leave it empty to detect the language from the browser via
  `Request::getPreferredLanguage()` instead of forcing a fixed tenant language (migration `NullableLanguage`)
- Renamed `UrlManager::setApplicationLanguage()` override to `UrlManager::setLanguage()`
- Removed default tenant

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