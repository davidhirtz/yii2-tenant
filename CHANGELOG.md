## 1.1.4 (Oct 1, 2024)

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