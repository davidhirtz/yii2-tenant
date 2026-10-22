<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Web;

use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Hirtz\Tenant\Web\UrlManager;
use Yii;
use yii\web\Cookie;
use yii\web\UrlNormalizerRedirectException;

final class UrlManagerTest extends TestCase
{
    use TenantFixtureTrait;

    public function testCreateUrl(): void
    {
        $manager = $this->getUrlManager();

        $url = $manager->createUrl('post/view');
        self::assertEquals('/post/view', $url);

        $url = $manager->createUrl(['post/view']);
        self::assertEquals('/post/view', $url);

        $manager = $this->getUrlManager([
            'baseUrl' => '/test/',
            'scriptUrl' => '/test',
            'enablePrettyUrl' => false,
        ]);

        $url = $manager->createUrl('post/view');
        self::assertEquals('/test?r=post%2Fview', $url);

        $url = $manager->createUrl(['post/view']);
        self::assertEquals('/test?r=post%2Fview', $url);
    }

    public function testCreateUrlWithParams(): void
    {
        $manager = $this->getUrlManager();

        $params = [
            'post/view',
            'id' => 1,
            'title' => 'sample post',
        ];

        $url = $manager->createUrl($params);
        self::assertEquals('/post/view?id=1&title=sample+post', $url);

        $params['tenant'] = $this->getTenantFromFixture('enabled');
        $url = $manager->createUrl($params);
        self::assertEquals('/de/post/view?id=1&title=sample+post', $url);

        $params['tenant'] = $this->getTenantFromFixture('draft');
        $url = $manager->createUrl($params);

        self::assertEquals('https://www.draft.localhost/post/view?id=1&title=sample+post', $url);
    }

    public function testTheTenantQueryParamIsKeptOnlyForAKnownTenant(): void
    {
        $urls = [];

        foreach (['2', 'junk', ['1']] as $tenant) {
            $this->getRequest(['queryParams' => ['tenant' => $tenant]]);
            $urls[] = $this->getUrlManager()->createUrl('post/view');
        }

        self::assertSame(['/post/view?tenant=2', '/post/view', '/post/view'], $urls);
    }

    public function testCreateAbsoluteUrl(): void
    {
        $manager = $this->getUrlManager();

        $url = $manager->createAbsoluteUrl('post/view');
        self::assertEquals('https://www.domain.localhost/post/view', $url);

        $url = $manager->createAbsoluteUrl(['post/view'], '');
        self::assertEquals('//www.domain.localhost/post/view', $url);
    }

    public function testCreateDraftUrl(): void
    {
        $manager = $this->getUrlManager();

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://draft.domain.localhost/post/view', $url);

        $url = $manager->createDraftUrl([
            'post/view',
            'tenant' => $this->getTenantFromFixture('draft'),
        ]);

        self::assertEquals('https://draft.draft.localhost/post/view', $url);

        $manager->draftSubdomain = 'preview';

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://preview.domain.localhost/post/view', $url);

        $this->getWebRequest()->setIsDraft(true);
        $manager->draftSubdomain = false;

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://www.domain.localhost/post/view', $url);
    }

    public function testTenantAsPath(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];

        $manager = $this->getUrlManager();
        self::assertEquals('https://www.domain.localhost', $manager->getHostInfo());

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/de',
        ]);

        $manager->parseRequest($request);

        $tenant = $this->getTenantFromFixture('enabled');

        self::assertEquals('https://www.domain.localhost', $manager->getHostInfo());
        self::assertEquals($tenant->id, $manager->tenant->id);
        self::assertEquals('de', Yii::$app->language);

        $url = $manager->createAbsoluteUrl(['test']);
        self::assertEquals('https://www.domain.localhost/de/test', $url);

        $url = $manager->createDraftUrl(['test']);
        self::assertEquals('https://draft.domain.localhost/de/test', $url);

        $request = $this->getRequest([
            'hostInfo' => 'https://draft.domain.localhost',
            'url' => '/de',
        ]);

        $manager->parseRequest($request);

        // The tenant's canonical host, not the request host with the draft prefix stripped.
        self::assertEquals('https://www.domain.localhost', $manager->getHostInfo());
        self::assertEquals('de', Yii::$app->language);
        self::assertTrue($request->getIsDraft());
        self::assertEquals($tenant->id, $manager->tenant->id);

        $request = $this->getRequest([
            'hostInfo' => 'https://www.draft.localhost',
        ]);

        $manager->parseRequest($request);

        self::assertEquals('en-US', Yii::$app->language);
        self::assertArrayHasKey('x-robots-tag', $this->getWebResponse()->getHeaders()->toArray());
    }

    public function testAPathTenantIsFoundDespiteAQueryString(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];
        $tenant = $this->getTenantFromFixture('enabled');

        foreach (['/de?utm_source=x', '/de?next=/a/b', '/de/page?a=1'] as $url) {
            $manager = $this->getUrlManager();
            $request = $this->getRequest([
                'hostInfo' => 'https://www.domain.localhost',
                'url' => $url,
            ]);

            $manager->parseRequest($request);

            self::assertSame($tenant->id, $manager->tenant?->id, $url);
            self::assertSame('de', Yii::$app->language, $url);
        }
    }

    public function testATenantWithoutAUrlKeepsTheRequestHost(): void
    {
        $tenant = $this->getTenantFromFixture();
        $tenant->url = null;

        self::assertNotFalse($tenant->update());

        $manager = $this->getUrlManager();

        $request = $this->getRequest([
            'hostInfo' => 'https://www.anything.localhost',
            'url' => '/',
        ]);

        $manager->parseRequest($request);

        self::assertEquals($tenant->id, $manager->tenant?->id);
        self::assertEquals('https://www.anything.localhost', $manager->getHostInfo());
        self::assertEquals('/post/view', $manager->createUrl('post/view'));
        self::assertEquals('https://www.anything.localhost/post/view', $manager->createAbsoluteUrl('post/view'));
        self::assertEquals('https://draft.anything.localhost/post/view', $manager->createDraftUrl('post/view'));
    }

    /**
     * A tenant without a language pins none, the way one without a URL leaves the request host alone — so the
     * configured default is what decides which language goes without a path prefix.
     */
    public function testATenantWithoutALanguageKeepsTheConfiguredDefaultLanguage(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];

        $tenant = $this->getTenantFromFixture();
        $tenant->language = null;

        self::assertNotFalse($tenant->update());

        $manager = $this->getUrlManager([
            'i18nUrl' => true,
            'defaultLanguage' => 'de',
        ]);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
        ]));

        self::assertEquals('de', $manager->defaultLanguage);
        self::assertEquals('de', Yii::$app->language);

        self::assertEquals('/post/view', $manager->createUrl(['post/view', 'language' => 'de']));
        self::assertEquals('/en/post/view', $manager->createUrl(['post/view', 'language' => 'en-US']));
    }

    public function testATenantWithLanguagesNarrowsTheLanguages(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['de', 'fr']);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/fr/',
        ]));

        self::assertSame(['de' => 'de', 'fr' => 'fr'], $manager->languages);
        self::assertSame('fr', Yii::$app->language);

        self::assertSame('/fr/post/view', $manager->createUrl(['post/view', 'language' => 'fr']));
        self::assertSame('/post/view', $manager->createUrl(['post/view', 'language' => 'it']));
    }

    public function testATenantWithLanguagesDetectsOnlyThoseFromTheBrowser(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['de', 'fr']);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
            'acceptableLanguages' => ['it', 'fr', 'de'],
        ]));

        self::assertSame('fr', Yii::$app->language);
    }

    public function testATenantWithoutLanguagesRestoresEveryLanguage(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['de', 'fr']);
        $manager->setTenant($this->getTenantFromFixture('enabled'));

        self::assertSame(['en-US' => 'en', 'de' => 'de', 'fr' => 'fr', 'it' => 'it'], $manager->languages);
    }

    public function testASingleLanguageTenantSetsItsLanguageWithoutAPrefix(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages([], 'fr');

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
        ]));

        self::assertSame(['fr' => 'fr'], $manager->languages);
        self::assertSame('fr', Yii::$app->language);
        self::assertSame('/post/view', $manager->createUrl(['post/view', 'language' => 'fr']));
    }

    public function testAOneLanguageTenantWithoutADefaultLinksWithoutAPrefix(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['fr']);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
        ]));

        self::assertSame('fr', Yii::$app->language);
        self::assertSame('/post/view', $manager->createUrl(['post/view']));
    }

    public function testAnotherTenantsUrlTakesThatTenantsLanguagePrefixes(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['de'], 'en-US');

        $other = $this->getTenantFromFixture('draft');
        $other->language = 'de';
        $other->languages = null;
        self::assertNotFalse($other->update());

        self::assertSame('https://www.draft.localhost/post/view', $manager->createUrl(['post/view', 'tenant' => $other, 'language' => 'de']));
        self::assertSame('https://www.draft.localhost/en/post/view', $manager->createUrl(['post/view', 'tenant' => $other, 'language' => 'en-US']));

        // the request's own tenant is left as it was
        self::assertSame('/de/post/view', $manager->createUrl(['post/view', 'language' => 'de']));
    }

    public function testTheCanonicalUrlIsTheDefaultTenantsWithoutAPrefix(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['de'], 'en-US');
        $manager->setTenant($this->getTenantFromFixture('draft'));
        Yii::$app->language = 'de';

        self::assertSame('https://www.domain.localhost/post/view', $manager->createCanonicalUrl(['post/view']));
    }

    public function testWithoutI18nUrlTheBrowserPicksAmongTheTenantsLanguages(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['fr'], 'de', false);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
            'acceptableLanguages' => ['it', 'fr'],
        ]));

        self::assertSame('fr', Yii::$app->language);
    }

    public function testWithoutI18nUrlTheDefaultIsTheFallback(): void
    {
        $manager = $this->getUrlManagerWithTenantLanguages(['fr'], 'de', false);

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
            'acceptableLanguages' => ['it'],
        ]));

        self::assertSame('de', Yii::$app->language);
    }

    public function testATenantWithoutALanguageKeepsTheConfiguredDefaultAcrossTenants(): void
    {
        Yii::$app->getI18n()->languages = ['de', 'en-US'];

        $manager = $this->getUrlManager(['i18nUrl' => true, 'defaultLanguage' => 'en-US']);
        self::assertSame('en-US', $manager->defaultLanguage);

        $manager->setTenant($this->getTenantFromFixture('enabled'));
        self::assertSame('de', $manager->defaultLanguage);

        $tenant = $this->getTenantFromFixture('draft');
        $tenant->language = null;
        $manager->setTenant($tenant);

        self::assertSame('en-US', $manager->defaultLanguage);
        self::assertSame('en-US', $manager->getApplicationDefaultLanguage());
    }

    /**
     * Every URL of a host has to write its cookies under one scope, or the same name exists twice in the browser
     * and a logout — which can only remove the scope of the URL it was called on — leaves the other one live.
     */
    public function testTheCookieDomainBelongsToTheHostRatherThanToTheMatchedTenant(): void
    {
        $tenant = $this->getTenantFromFixture();
        $tenant->url = null;

        self::assertNotFalse($tenant->update());
        TenantCollection::invalidateCache();

        $manager = $this->getUrlManager();

        // A URL only the default tenant answers for, on the host a path tenant carries a cookie domain for.
        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/admin',
        ]));

        self::assertSame($tenant->id, $manager->tenant?->id);
        self::assertSame('.domain.localhost', Yii::createObject(Cookie::class)->domain);
    }

    public function testAnExplicitCookieDomainWinsOverTheHostAnotherTenantDerives(): void
    {
        $manager = $this->getUrlManager();

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'url' => '/',
        ]));

        self::assertSame('.domain.localhost', Yii::createObject(Cookie::class)->domain);
    }

    public function testTheDraftHostSharesTheCookieDomainOfTheTenantItStandsIn(): void
    {
        $manager = $this->getUrlManager();

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://draft.domain.localhost',
            'url' => '/admin',
        ]));

        self::assertSame('.domain.localhost', Yii::createObject(Cookie::class)->domain);
    }

    public function testAHostNoTenantNamesGetsNoCookieDomain(): void
    {
        $manager = $this->getUrlManager();

        $manager->parseRequest($this->getRequest([
            'hostInfo' => 'https://www.anything.localhost',
            'url' => '/',
        ]));

        self::assertSame('', Yii::createObject(Cookie::class)->domain);
    }

    public function testRedirectMap(): void
    {
        $manager = $this->getUrlManager([
            'redirectMap' => [
                'old-url' => 'https://www.new-domain.localhost/new-url',
                [
                    'request' => ['old/*'],
                    'url' => 'temp/',
                    'code' => 302,
                ],
            ],
        ]);

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
        ]);

        $manager->parseRequest($request);
        self::assertEquals('https://www.domain.localhost', $manager->getHostInfo());

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'pathInfo' => '/old-url',
        ]);

        try {
            $manager->parseRequest($request);
            self::fail('UrlNormalizerRedirectException not thrown');
        } catch (UrlNormalizerRedirectException $e) {
            self::assertEquals('https://www.new-domain.localhost/new-url', $e->url);
        }

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.localhost',
            'pathInfo' => '/old/test',
        ]);

        try {
            $manager->parseRequest($request);
            self::fail('UrlNormalizerRedirectException not thrown');
        } catch (UrlNormalizerRedirectException $e) {
            self::assertEquals('/temp/test', $e->url);
            self::assertEquals(302, $e->statusCode);
        }
    }

    public function testImmutableRuleParams(): void
    {
        $manager = $this->getUrlManager([
            'rules' => [
                'index' => 'site/index',
                'view/<slug>' => 'site/view',
            ],
        ]);

        self::assertEquals(['index', 'view'], $manager->getImmutableRuleParams());

        $manager = $this->getUrlManager([
            'rules' => [
                '<type:(blog|archive)>/<slug>' => 'site/view',
            ],
        ]);

        self::assertEquals(['blog', 'archive'], $manager->getImmutableRuleParams());

        $manager = $this->getUrlManager([
            'rules' => [
                '<filter:new-posts|old_posts>/<slug>' => 'site/view',
            ],
        ]);

        self::assertEquals(['new-posts', 'old_posts'], $manager->getImmutableRuleParams());
    }

    /**
     * @param array<string, mixed> $config
     */
    protected function getRequest($config = []): Request
    {
        Yii::$app->set('request', [
            'class' => Request::class,
            'baseUrl' => '',
            'url' => '/',
            ...$config,
        ]);

        return $this->getWebRequest();
    }

    /**
     * @param list<string> $languages
     */
    private function getUrlManagerWithTenantLanguages(array $languages, ?string $language = null, bool $i18nUrl = true): UrlManager
    {
        Yii::$app->getI18n()->languages = ['en-US', 'de', 'fr', 'it'];

        $tenant = $this->getTenantFromFixture();
        $tenant->language = $language;
        $tenant->languages = $languages;

        self::assertNotFalse($tenant->update());

        return $this->getUrlManager(['i18nUrl' => $i18nUrl]);
    }

    /**
     * @param array<string, mixed> $config
     */
    private function getUrlManager($config = []): UrlManager
    {
        Yii::$app->set('urlManager', [
            'class' => UrlManager::class,
            ...$config,
        ]);

        /** @var UrlManager $manager */
        $manager = Yii::$app->getUrlManager();
        $manager->setTenant(TenantCollection::getDefault());

        return $manager;
    }
}
