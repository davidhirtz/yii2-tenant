<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Modules\Admin\Controllers;

use Hirtz\Skeleton\I18n\Lang;
use Hirtz\Skeleton\Models\Forms\DeleteForm;
use Hirtz\Skeleton\Web\Controller;
use Hirtz\Skeleton\Widgets\Flashes;
use Hirtz\Tenant\Models\Actions\ReorderTenants;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Modules\Admin\Controllers\Traits\TenantControllerTrait;
use Hirtz\Tenant\Modules\Admin\Data\TenantActiveDataProvider;
use Override;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class TenantController extends Controller
{
    use TenantControllerTrait;

    #[Override]
    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'actions' => ['create'],
                        'roles' => [Tenant::AUTH_TENANT_CREATE],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['index', 'order', 'update'],
                        'roles' => [Tenant::AUTH_TENANT_UPDATE],
                    ],
                    [
                        'allow' => true,
                        'actions' => ['delete'],
                        'roles' => [Tenant::AUTH_TENANT_DELETE],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                    'order' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex(?int $status = null, ?string $q = null): string
    {
        $provider = Yii::$container->get(TenantActiveDataProvider::class, [], [
            'searchString' => $q,
            'status' => $status,
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $tenant = Tenant::create();
        $tenant->loadDefaultValues();

        if ($tenant->load($this->request->post()) && !$this->request->isFormReload() && $tenant->insert()) {
            $this->success(Lang::t('tenant', 'TENANT_SUCCESS_CREATED'));
            return $this->redirect($tenant->getAdminRoute());
        }

        $tenant->url ??= $this->request->getHostInfo();

        return $this->render('create', [
            'tenant' => $tenant,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $tenant = $this->findTenant($id, Tenant::AUTH_TENANT_UPDATE);

        if ($tenant->load($this->request->post()) && !$this->request->isFormReload() && $tenant->update()) {
            $this->success(Lang::t('tenant', 'TENANT_SUCCESS_UPDATED'));
            return $this->redirect($tenant->getAdminRoute());
        }

        return $this->render('update', [
            'tenant' => $tenant,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $tenant = $this->findTenant($id, Tenant::AUTH_TENANT_DELETE);
        $form = new DeleteForm($tenant, 'name');

        if ($form->load($this->request->post()) && $form->delete()) {
            $this->success(Lang::t('tenant', 'TENANT_SUCCESS_DELETED'));
            return $this->redirect(['index', 'tenant' => TenantCollection::getDefault()]);
        }

        $this->error($form);
        return $this->redirect(['update', 'id' => $id]);
    }

    /**
     * @noinspection PhpUnused
     */
    public function actionOrder(): string
    {
        $success = ReorderTenants::runWithBodyParam('tenant');

        if ($success) {
            $this->success(Lang::t('tenant', 'TENANT_SUCCESS_ORDERED'));
        }

        return (string) Flashes::make();
    }
}
