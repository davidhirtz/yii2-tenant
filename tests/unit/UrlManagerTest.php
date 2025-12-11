<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Unit;

use Hirtz\Skeleton\Web\Request;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Hirtz\Tenant\web\UrlManager;
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

        self::assertEquals('https://www.draft.com/post/view?id=1&title=sample+post', $url);
    }

    public function testCreateAbsoluteUrl(): void
    {
        $manager = $this->getUrlManager();

        $url = $manager->createAbsoluteUrl('post/view');
        self::assertEquals('https://www.domain.com/post/view', $url);

        $url = $manager->createAbsoluteUrl(['post/view'], '');
        self::assertEquals('//www.domain.com/post/view', $url);
    }

    public function testCreateDraftUrl(): void
    {
        $manager = $this->getUrlManager();

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://draft.domain.com/post/view', $url);

        $url = $manager->createDraftUrl([
            'post/view',
            'tenant' => $this->getTenantFromFixture('draft'),
        ]);

        self::assertEquals('https://draft.draft.com/post/view', $url);

        $manager->draftSubdomain = 'preview';

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://preview.domain.com/post/view', $url);

        Yii::$app->getRequest()->setIsDraft(true);
        $manager->draftSubdomain = false;

        $url = $manager->createDraftUrl('post/view');
        self::assertEquals('https://www.domain.com/post/view', $url);
    }

    public function testTenantAsPath(): void
    {
        $manager = $this->getUrlManager();
        self::assertEquals('https://www.domain.com', $manager->getHostInfo());

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.com',
            'url' => '/de',
        ]);

        $manager->parseRequest($request);

        $tenant = $this->getTenantFromFixture('enabled');

        self::assertEquals('https://www.domain.com', $manager->getHostInfo());
        self::assertEquals('de', Yii::$app->language);
        self::assertEquals($tenant->id, $manager->tenant->id);

        $url = $manager->createAbsoluteUrl(['test']);
        self::assertEquals('https://www.domain.com/de/test', $url);

        $url = $manager->createDraftUrl(['test']);
        self::assertEquals('https://draft.domain.com/de/test', $url);

        $request = $this->getRequest([
            'hostInfo' => 'https://draft.domain.com',
            'url' => '/de',
        ]);

        $manager->parseRequest($request);

        self::assertEquals('https://domain.com', $manager->getHostInfo());
        self::assertEquals('de', Yii::$app->language);
        self::assertTrue($request->getIsDraft());
        self::assertEquals($tenant->id, $manager->tenant->id);

        $request = $this->getRequest([
            'hostInfo' => 'https://www.draft.com',
        ]);

        $manager->parseRequest($request);

        self::assertEquals('en-US', Yii::$app->language);
        self::assertArrayHasKey('x-robots-tag', Yii::$app->getResponse()->getHeaders()->toArray());
    }

    public function testRedirectMap(): void
    {
        $manager = $this->getUrlManager([
            'redirectMap' => [
                'old-url' => 'https://www.new-domain.com/new-url',
                [
                    'request' => ['old/*'],
                    'url' => 'temp/',
                    'code' => 302,
                ],
            ],
        ]);

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.com',
        ]);

        $manager->parseRequest($request);
        self::assertEquals('https://www.domain.com', $manager->getHostInfo());

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.com',
            'pathInfo' => '/old-url',
        ]);

        try {
            $manager->parseRequest($request);
            self::fail('UrlNormalizerRedirectException not thrown');
        } catch (UrlNormalizerRedirectException $e) {
            self::assertEquals('https://www.new-domain.com/new-url', $e->url);
        }

        $request = $this->getRequest([
            'hostInfo' => 'https://www.domain.com',
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

    protected function getRequest($config = []): Request
    {
        Yii::$app->set('request', [
            'class' => Request::class,
            'url' => '/',
            ...$config,
        ]);

        return Yii::$app->getRequest();
    }

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
