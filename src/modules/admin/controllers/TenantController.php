<?php

namespace davidhirtz\yii2\tenant\modules\admin\controllers;

use davidhirtz\yii2\skeleton\models\forms\DeleteForm;
use davidhirtz\yii2\skeleton\web\Controller;
use davidhirtz\yii2\tenant\models\Tenant;
use davidhirtz\yii2\tenant\modules\admin\controllers\traits\TenantControllerTrait;
use davidhirtz\yii2\tenant\modules\admin\data\TenantActiveDataProvider;
use Yii;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\Response;

class TenantController extends Controller
{
    use TenantControllerTrait;

    public function behaviors(): array
    {
        return array_merge(parent::behaviors(), [
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
                        'actions' => ['index', 'update'],
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
                ],
            ],
        ]);
    }

    public function init(): void
    {
        $this->setViewPath('@views/modules/admin/tenant');
        parent::init();
    }

    public function actionIndex(?string $q = null): string
    {
        $provider = Yii::$container->get(TenantActiveDataProvider::class, [], [
            'searchString' => $q,
        ]);

        return $this->render('index', [
            'provider' => $provider,
        ]);
    }

    public function actionCreate(): Response|string
    {
        $tenant = Tenant::create();
        $tenant->loadDefaultValues();

        if ($tenant->load(Yii::$app->getRequest()->post()) && $tenant->insert()) {
            $this->success(Yii::t('tenant', 'TENANT_FLASH_CREATED'));
            return $this->redirect($tenant->getAdminRoute());
        }

        return $this->render('create', [
            'tenant' => $tenant,
        ]);
    }

    public function actionUpdate(int $id): Response|string
    {
        $tenant = $this->findTenant($id, Tenant::AUTH_TENANT_UPDATE);

        if ($tenant->load(Yii::$app->getRequest()->post()) && $tenant->update()) {
            $this->success(Yii::t('tenant', 'TENANT_FLASH_UPDATED'));
            return $this->redirect($tenant->getAdminRoute());
        }

        return $this->render('update', [
            'tenant' => $tenant,
        ]);
    }

    public function actionDelete(int $id): Response
    {
        $tenant = $this->findTenant($id, Tenant::AUTH_TENANT_DELETE);
        $form = new DeleteForm(['model' => $tenant]);

        if ($form->load(Yii::$app->getRequest()->post()) && $form->delete()) {
            $this->success(Yii::t('tenant', 'TENANT_FLASH_DELETED'));
            return $this->redirect(['index']);
        }

        $this->error($form);
        return $this->redirect(['update', 'id' => $id]);
    }
}
