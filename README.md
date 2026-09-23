# yii2-tenant

Multi-tenancy for the `davidhirtz/yii2-skeleton` platform: one installation serves several sites, each a
`Models\Tenant` row with its own host or path, language and cookie scope. The bundle resolves the tenant of every
web request, scopes generated URLs, the page cache and the sitemap to it, and ships the admin pages that manage
tenants. It depends on `davidhirtz/yii2-skeleton` alone. `davidhirtz/yii2-cms` requires it: every entry belongs
to a tenant (`entry.tenant_id`) and every permalink is unique per tenant, so a cms installation always has this
bundle, and an installation that wants no second site simply keeps the one seeded row.

## Installation

```bash
composer require davidhirtz/yii2-tenant
./yii migrate
```

The bundle bootstraps itself through `extra.bootstrap` (`Bootstrap`): it registers the `@tenant` alias, the
`tenant` message category, the application module `tenant`, its migration namespace and, unless the project has
bound them itself, three container definitions (see below). `./yii migrate` creates the `tenant` table and the
`tenant` permission, and seeds one enabled tenant named after the application with neither URL nor language
(`Migrations\M260101000110TenantSeed`). Such a tenant pins nothing: the site keeps answering on whatever host it
is served on, which is what a single-site installation wants.

## Configuration

`modules.tenant` (`Module`):

| Property            | Default | Meaning                                                                                         |
|---------------------|---------|-------------------------------------------------------------------------------------------------|
| `enableAdminModule` | `true`  | Registers the admin submodule `admin/tenant` and its dashboard role; off, its routes answer 404. |

```php
'modules' => [
    'tenant' => [
        'enableAdminModule' => false,
    ],
],
```

Container definitions `Bootstrap` sets when the project has not bound the class itself:

| Skeleton class                     | Bound to            |
|------------------------------------|---------------------|
| `Hirtz\Skeleton\Web\UrlManager`    | `Web\UrlManager`    |
| `Hirtz\Skeleton\Filters\PageCache` | `Filters\PageCache` |
| `Hirtz\Skeleton\Registry\Report`   | `Registry\Report`   |

Components the bundle touches: `urlManager` (through the container definition), `sitemap` (its `variations` is
set to a callback answering the current tenant's id), `i18n` (the `tenant` translation) and `cache`
(`Models\Collections\TenantCollection` caches the tenants under the tag `TenantCollection::CACHE_KEY`).
The bundle reads no `params` and registers no console command.

`Models\Tenant` has no types, so a project declares its custom attributes through the container, as a closure:

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

They render in the admin form (`Modules\Admin\Widgets\Forms\TenantActiveForm`) and are stored in
`tenant.custom_attributes`. A project needing more binds `Tenant::class` to a subclass instead.

## Tenants

| Column              | Meaning                                                                                                                                                                    |
|---------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `name`              | Required; the admin label and the name the registry report lists.                                                                                                          |
| `status`            | Enabled, draft or disabled (`DraftStatusAttributeInterface`). A disabled tenant is never loaded; a draft tenant serves with `X-Robots-Tag: none`.                          |
| `url`               | Optional canonical URL, a host (`https://www.example.com`) or a host plus one path segment (`https://www.example.com/de`). Empty means "pin no host".                       |
| `language`          | Optional; becomes the URL manager's `defaultLanguage`. Empty keeps the configured one, which falls back to the browser's preferred language.                               |
| `cookie_domain`     | Optional; must lie within the URL's host. Empty derives the host of `url`.                                                                                                 |
| `custom_attributes` | The JSON column behind the custom attributes above.                                                                                                                        |
| `position`          | The order of the admin grid; `TenantCollection::getDefault()` is the first enabled tenant.                                                                                 |
| `entry_count`       | Maintained by `yii2-cms`.                                                                                                                                                  |

`Tenant::beforeDelete()` refuses the last tenant (`TENANT_ERROR_DELETE_LAST`).

### Resolving the tenant

`Web\UrlManager::parseRequest()` looks the absolute request URL up in `TenantCollection::getByUrl()`, then
shortens it segment by segment so a path tenant matches, and falls back to `TenantCollection::getDefault()`.
It then strips the tenant's path from the request's `pathInfo`, keeps the tenant's host as the URL manager's
`hostInfo` for the whole request (also on a host that only fell back to the default tenant), takes the tenant's
language as `defaultLanguage`, and sets the cookie domain the request's host resolves to. The cookie scope is
per host, not per matched tenant (`TenantCollection::getCookieDomainByHostInfo()`): a path tenant shares its
host with every other URL of the installation, and a tenant naming an explicit `cookie_domain` wins.

The skeleton's `draftSubdomain` (`draft` by default) is honoured: a request on `draft.example.com` matches the
tenant at `www.example.com` or `example.com`, and `Web\UrlManager::getDraftHostInfo()` answers the tenant's host
with that subdomain. A *draft* tenant is a different thing, a `status`, and is what the `X-Robots-Tag` is for.

The current tenant is a property of the URL manager, `null` under a console application:

```php
$manager = Yii::$app->getUrlManager();
$tenant = $manager instanceof \Hirtz\Tenant\Web\UrlManager ? $manager->tenant : null;
```

### URLs

A route may carry a `Tenant` under the key `tenant`: `Url::to(['/site/index', 'tenant' => $tenant])` prepends the
tenant's path and, when it differs from the current one, its host; `$tenant->getAbsoluteUrl()` is its front page.
A `tenant` query parameter holding an id is carried into every generated URL of that request, which is how the
admin switches between tenants (`TenantCollection::getFromRequest()`, the grid's *Switch to tenant* button), and
`Web\UrlManager::createUrl()` works without a web request, so a console command can build tenant URLs.

A tenant's `url` is validated against the other tenants and against the web root: a path segment that names an
immutable URL rule, a file or a directory under `@webroot` is refused (`TENANT_ERROR_PATH_PROTECTED`).

### Models

`Models\Traits\TenantRelationTrait` gives a model its `tenant` relation and `populateTenantRelation()`; the
model declares the `tenant_id` column itself. `Models\Queries\Traits\TenantQueryTrait` gives its query
`andWhereTenant()` and `andWhereCurrentTenant()`, a no-op without a current tenant.

### Page cache, sitemap and registry

`Filters\PageCache` appends the current tenant's id to the filter's `variations`, so the same route is cached
per tenant, and the `sitemap` component's `variations` callback does the same for the sitemap. `Registry\Report`
gives `./yii registry/push` the first tenant with a URL as the installation's `url` and lists every one under
`extra.tenants` as `{name, url, status}`; `--url` still wins.

### Permissions and admin

One permission, `Tenant::AUTH_TENANT` (`tenant`, described by `AUTH_TENANT_DESCRIPTION`), guards every tenant
action: create, update, delete, reorder and the grid's status toggle. The migration grants it to `admin` alone;
`manager` does not hold it, since a tenant cuts the whole installation. The admin lives under
`admin/tenant/tenant/<action>` (`Modules\Admin\Controllers\TenantController`), with `TenantGridView`,
`TenantActiveForm` and the `TenantHeader` in `Modules\Admin\Widgets\`. The grid's status toggle is turned off
with `[TenantGridView::class => ['enableStatusUpdate' => false]]` in the container.

### Testing

`Test\Traits\TenantFixtureTrait` loads `Test\Fixtures\TenantFixture`, three tenants keyed `default`, `enabled`
(a path tenant with its own cookie domain) and `draft`, reachable through `getTenantFromFixture()`. The fixture
clears the seeded tenant first and invalidates the collection cache. `Test\TestCase` is the base for the bundle's
own tests.
