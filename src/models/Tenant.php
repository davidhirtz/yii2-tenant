<?php

namespace davidhirtz\yii2\tenant\models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use davidhirtz\yii2\skeleton\behaviors\BlameableBehavior;
use davidhirtz\yii2\skeleton\behaviors\TimestampBehavior;
use davidhirtz\yii2\skeleton\behaviors\TrailBehavior;
use davidhirtz\yii2\skeleton\db\ActiveRecord;
use davidhirtz\yii2\skeleton\models\interfaces\StatusAttributeInterface;
use davidhirtz\yii2\skeleton\models\traits\StatusAttributeTrait;
use davidhirtz\yii2\skeleton\models\traits\UpdatedByUserTrait;
use davidhirtz\yii2\skeleton\validators\DynamicRangeValidator;
use davidhirtz\yii2\tenant\models\collections\TenantCollection;
use davidhirtz\yii2\tenant\models\queries\TenantQuery;
use Yii;

/**
 * @property int $id
 * @property int $status
 * @property string $name
 * @property string $url
 * @property string $cookie_domain
 * @property string $language
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 */
class Tenant extends ActiveRecord implements StatusAttributeInterface
{
    use StatusAttributeTrait;
    use UpdatedByUserTrait;

    final public const AUTH_TENANT_CREATE = 'tenantCreate';
    final public const AUTH_TENANT_DELETE = 'tenantDelete';
    final public const AUTH_TENANT_UPDATE = 'tenantUpdate';

    private ?string $_hostInfo = null;
    private ?string $_pathInfo = null;

    public function behaviors(): array
    {
        return [
            ...parent::behaviors(),
            'BlameableBehavior' => BlameableBehavior::class,
            'DateTimeBehavior' => DateTimeBehavior::class,
            'TimestampBehavior' => TimestampBehavior::class,
            'TrailBehavior' => TrailBehavior::class,
        ];
    }

    public function rules(): array
    {
        return [
            ...parent::rules(),
            [
                ['status', 'name', 'url'],
                'required',
            ],
            [
                ['status'],
                DynamicRangeValidator::class,
            ],
            [
                ['name', 'cookie_domain'],
                'string',
                'max' => 255,
            ],
            [
                ['url', 'cookie_domain'],
                'trim',
            ],
            [
                ['url'],
                'string',
                'max' => 100,
            ],
            [
                ['url'],
                'url',
            ],
            [
                ['url'],
                $this->validateUrl(...),
            ],
            [
                ['cookie_domain'],
                $this->validateCookieDomain(...),
            ],
            [
                ['language'],
                DynamicRangeValidator::class,
            ],
        ];
    }

    public function beforeValidate(): bool
    {
        $this->_hostInfo = null;
        $this->_pathInfo = null;

        return parent::beforeValidate();
    }

    public function validateUrl(): void
    {
        if ($this->hasErrors('url')) {
            return;
        }

        $this->url = trim(strtok($this->url, '?'), '/ ');

        if (str_contains($this->url, '//draft.')) {
            $this->addInvalidAttributeError('url');
            return;
        }

        $tenant = TenantCollection::getByUrl($this->url);

        if ($tenant && $tenant->id != $this->id) {
            $this->addError('url', Yii::t('yii', '{attribute} "{value}" has already been taken.', [
                'attribute' => $this->getAttributeLabel('url'),
                'value' => $this->url,
            ]));

            return;
        }

        $path = parse_url($this->url, PHP_URL_PATH);

        if ($path) {
            $param = explode('/', $path)[1];
            $path = Yii::getAlias("@webroot/$param");

            if (
                in_array($param, Yii::$app->getUrlManager()->getImmutableRuleParams())
                || is_dir($path)
                || is_file($path)
            ) {
                $this->addError('url', Yii::t('tenant', 'TENANT_ERROR_PATH_PROTECTED', [
                    'path' => $param,
                ]));
            }
        }
    }

    public function validateCookieDomain(): void
    {
        if (
            str_starts_with($this->cookie_domain, 'http')
            || !str_contains($this->url, ltrim($this->cookie_domain, '.'))
            || !preg_match('/^[a-z.]/', $this->cookie_domain)
        ) {
            $this->addInvalidAttributeError('cookie_domain');
        }
    }

    public function afterSave($insert, $changedAttributes): void
    {
        TenantCollection::invalidateCache();
        parent::afterSave($insert, $changedAttributes);
    }

    public function isDeletable(): bool
    {
        return static::find()->count() > 1;
    }

    public static function find(): TenantQuery
    {
        return Yii::createObject(TenantQuery::class, [static::class]);
    }

    public function getAbsoluteUrl(): string
    {
        return Yii::$app->getUrlManager()->createAbsoluteUrl(['/', 'tenant' => $this]);
    }

    /**
     * @see TenantController::actionUpdate()
     */
    public function getAdminRoute(): array
    {
        return ['/admin/tenant/update', 'id' => $this->id];
    }

    public function getCookieDomain(): string
    {
        return $this->cookie_domain ?? parse_url($this->url, PHP_URL_HOST);
    }

    public function getHostInfo(): string
    {
        if ($this->_hostInfo === null) {
            $scheme = parse_url($this->url, PHP_URL_SCHEME);
            $this->_hostInfo = ($scheme ? "$scheme://" : '//') . parse_url($this->url, PHP_URL_HOST);
        }

        return $this->_hostInfo;
    }

    public function getPathInfo(): string
    {
        $this->_pathInfo ??= parse_url(trim($this->url, '/'), PHP_URL_PATH) ?? '';
        return $this->_pathInfo;
    }

    /**
     * @noinspection PhpUnused
     */
    public function getTrailAttributes(): array
    {
        return [
            'status',
            'name',
            'url',
        ];
    }

    /**
     * @noinspection PhpUnused
     */
    public function getTrailModelName(): string
    {
        return $this->name ?? $this->getTrailModelType();
    }

    public function getTrailModelType(): string
    {
        return Yii::t('tenant', 'TENANT_NAME');
    }

    /**
     * @noinspection PhpUnused
     */
    public function getTrailModelAdminRoute(): array|false
    {
        return $this->getAdminRoute();
    }

    public static function getLanguages(): array
    {
        $i18n = Yii::$app->getI18n();
        $languages = [];

        foreach ($i18n->getLanguages() as $language) {
            $languages[$language]['name'] = $i18n->getLabel($language);
        }

        return $languages;
    }

    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'name' => Yii::t('tenant', 'TENANT_LABEL_NAME'),
            'url' => Yii::t('tenant', 'TENANT_LABEL_URL'),
            'language' => Yii::t('tenant', 'TENANT_LABEL_LANGUAGE'),
            'cookie_domain' => Yii::t('tenant', 'TENANT_LABEL_COOKIE_DOMAIN'),
        ];
    }

    public static function tableName(): string
    {
        return '{{%tenant}}';
    }
}
