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