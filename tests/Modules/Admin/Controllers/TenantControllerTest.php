<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Modules\Admin\Controllers;

use Hirtz\Skeleton\Models\User;
use Hirtz\Skeleton\Test\Fixtures\UserFixture;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;
use Yii;
use yii\web\ForbiddenHttpException;
use yii\web\MethodNotAllowedHttpException;
use yii\web\NotFoundHttpException;
use yii\web\Response;

class TenantControllerTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'tenant' => TenantFixture::class,
            'user' => UserFixture::class,
        ];
    }

    /**
     * The fixture's second tenant is German, and a tenant may only name a language the application has.
     */
    #[Override]
    protected function setUp(): void
    {
        parent::setUp();
        Yii::$app->getI18n()->setLanguages(['en-US', 'de']);
    }

    #[Override]
    protected function tearDown(): void
    {
        TenantCollection::invalidateCache();
        parent::tearDown();
    }

    public function testIndexListsEveryTenant(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/tenant/tenant/index');

        self::assertIsString($html);
        self::assertStringContainsString('Default', $html);
        self::assertStringContainsString('German Tenant', $html);
        self::assertStringContainsString('Draft Tenant', $html);
    }

    public function testIndexFiltersByStatus(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/tenant/tenant/index', ['status' => Tenant::STATUS_DRAFT]);

        self::assertIsString($html);
        self::assertStringContainsString('Draft Tenant', $html);
        self::assertStringNotContainsString('German Tenant', $html);
    }

    public function testIndexSearchesTheName(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/tenant/tenant/index', ['q' => 'German']);

        self::assertIsString($html);
        self::assertStringContainsString('German Tenant', $html);
        self::assertStringNotContainsString('Draft Tenant', $html);
    }

    public function testIndexIsForbiddenWithoutThePermission(): void
    {
        $this->getWebUser()->setIdentity($this->getUserFromFixture('admin'));

        $this->expectException(ForbiddenHttpException::class);
        Yii::$app->runAction('admin/tenant/tenant/index');
    }

    /**
     * The form opens on the host the administrator is already on, which is the usual answer.
     */
    public function testCreateSuggestsTheCurrentHost(): void
    {
        $this->login();

        $html = Yii::$app->runAction('admin/tenant/tenant/create');

        self::assertIsString($html);
        self::assertStringContainsString('name="Tenant[url]"', $html);
        self::assertStringContainsString($this->getWebRequest()->getHostInfo(), $html);
    }

    public function testCreateInsertsTheTenant(): void
    {
        $this->login();

        $response = $this->post('admin/tenant/tenant/create', [], [
            'Tenant' => [
                'status' => Tenant::STATUS_ENABLED,
                'name' => 'A new tenant',
                'url' => 'https://www.new.localhost',
                'language' => 'en-US',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);
        self::assertNotNull(Tenant::findOne(['name' => 'A new tenant']));
        self::assertNotEmpty($this->getWebSession()->getFlash('success'));
    }

    public function testAFormReloadDoesNotSave(): void
    {
        $this->login();

        $html = $this->post('admin/tenant/tenant/create', [], [
            'Tenant' => [
                'status' => Tenant::STATUS_ENABLED,
                'name' => 'Not saved',
                'url' => 'https://www.notsaved.localhost',
                'language' => 'en-US',
            ],
        ], reload: true);

        self::assertIsString($html);
        self::assertNull(Tenant::findOne(['name' => 'Not saved']));
    }

    public function testTwoTenantsCannotShareAUrl(): void
    {
        $this->login();

        $html = $this->post('admin/tenant/tenant/create', [], [
            'Tenant' => [
                'status' => Tenant::STATUS_ENABLED,
                'name' => 'A duplicate',
                'url' => 'https://www.domain.localhost',
                'language' => 'en-US',
            ],
        ]);

        self::assertIsString($html);
        self::assertNull(Tenant::findOne(['name' => 'A duplicate']));
    }

    public function testUpdateSavesTheTenant(): void
    {
        $this->login();

        $response = $this->post('admin/tenant/tenant/update', ['id' => 2], [
            'Tenant' => [
                'status' => Tenant::STATUS_ENABLED,
                'name' => 'Renamed',
                'url' => 'https://www.domain.localhost/de',
                'language' => 'de',
            ],
        ]);

        self::assertInstanceOf(Response::class, $response);
        self::assertSame('Renamed', Tenant::findOne(2)->name);
    }

    public function testUpdateOfAnUnknownTenantIsNotFound(): void
    {
        $this->login();

        $this->expectException(NotFoundHttpException::class);
        Yii::$app->runAction('admin/tenant/tenant/update', ['id' => 99999]);
    }

    /**
     * A tenant is deleted by typing its name, so a wrong answer sends the administrator back to it.
     */
    public function testDeleteNeedsTheTenantsNameTyped(): void
    {
        $this->login();

        $this->post('admin/tenant/tenant/delete', ['id' => 3], ['value' => 'Wrong']);

        self::assertNotNull(Tenant::findOne(3));
        self::assertNotEmpty($this->getWebSession()->getFlash('danger'));

        $this->post('admin/tenant/tenant/delete', ['id' => 3], ['value' => 'Draft Tenant']);

        self::assertNull(Tenant::findOne(3));
        self::assertNotEmpty($this->getWebSession()->getFlash('success'));
    }

    public function testDeleteRefusesAGetRequest(): void
    {
        $this->login();

        $this->expectException(MethodNotAllowedHttpException::class);
        Yii::$app->runAction('admin/tenant/tenant/delete', ['id' => 3]);
    }

    public function testOrderRewritesThePositions(): void
    {
        $this->login();

        $html = $this->post('admin/tenant/tenant/order', [], [
            'tenant' => [3, 2, 1],
        ]);

        self::assertIsString($html);
        self::assertLessThan(Tenant::findOne(1)->position, Tenant::findOne(3)->position);
        self::assertNotEmpty($this->getWebSession()->getFlash('success'));
    }

    /**
     * @param array<string, mixed> $params
     * @param array<string, mixed> $bodyParams
     */
    private function post(string $route, array $params = [], array $bodyParams = [], bool $reload = false): mixed
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $request = $this->getWebRequest();
        $request->setBodyParams([...$bodyParams, $request->csrfParam => $request->getCsrfToken()]);

        if ($reload) {
            $request->getHeaders()->set('X-Form-Reload', 'true');
        }

        return Yii::$app->runAction($route, $params);
    }

    private function login(): User
    {
        $user = $this->getUserFromFixture('admin');

        $permission = Yii::$app->getAuthManager()->getPermission(Tenant::AUTH_TENANT);
        Yii::$app->getAuthManager()->assign($permission, $user->id);

        $this->getWebUser()->setIdentity($user);

        return $user;
    }

    private function getUserFromFixture(string $key): User
    {
        /** @var UserFixture $fixture */
        $fixture = $this->getFixture('user');

        return User::findOne($fixture->data[$key]['id']);
    }
}
