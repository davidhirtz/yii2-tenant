<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Web;

use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Hirtz\Tenant\Web\UrlManager;
use Yii;
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
