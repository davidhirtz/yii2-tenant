<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Models;

use davidhirtz\yii2\datetime\DateTime;
use davidhirtz\yii2\datetime\DateTimeBehavior;
use Hirtz\Skeleton\Behaviors\BlameableBehavior;
use Hirtz\Skeleton\Behaviors\TimestampBehavior;
use Hirtz\Skeleton\Behaviors\TrailBehavior;
use Hirtz\Skeleton\Db\ActiveRecord;
use Hirtz\Skeleton\Models\Interfaces\CustomAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\DraftStatusAttributeInterface;
use Hirtz\Skeleton\Models\Interfaces\TrailModelInterface;
use Hirtz\Skeleton\Models\Traits\AdminModelTrait;
use Hirtz\Skeleton\Models\Traits\CustomAttributesTrait;
use Hirtz\Skeleton\Models\Traits\DraftStatusAttributeTrait;
use Hirtz\Skeleton\Models\Traits\TrailModelTrait;
use Hirtz\Skeleton\Models\Traits\UpdatedByUserTrait;
use Hirtz\Skeleton\Validators\DynamicRangeValidator;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Queries\TenantQuery;
use Hirtz\Tenant\Modules\ModuleTrait;
use Override;
use Yii;

/**
 * @property int $id
 * @property int $status
 * @property string $name
 * @property string|null $url
 * @property string|null $cookie_domain
 * @property string|null $language
 * @property int|false $position
 * @property int $updated_by_user_id
 * @property DateTime $updated_at
 * @property DateTime $created_at
 */
class Tenant extends ActiveRecord implements
    CustomAttributeInterface,
    DraftStatusAttributeInterface,
    TrailModelInterface
{
    use AdminModelTrait;
    use CustomAttributesTrait;
    use DraftStatusAttributeTrait;
    use ModuleTrait;
    use TrailModelTrait;
    use UpdatedByUserTrait;

    final public const string AUTH_TENANT = 'tenant';

    private ?string $hostInfo = null;
    private ?string $pathInfo = null;

    #[Override]
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

    #[Override]
    public function rules(): array
    {
        return [
            ...parent::rules(),
            [
                ['status', 'name'],
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
                'default',
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
                'default',
            ],
            [
                ['language'],
                DynamicRangeValidator::class,
                'integerOnly' => false,
            ],
        ];
    }

    #[Override]
    public function beforeValidate(): bool
    {
        $this->hostInfo = null;
        $this->pathInfo = null;

        return parent::beforeValidate();
    }

    public function validateUrl(): void
    {
        if ($this->hasErrors('url')) {
            return;
        }

        $this->url = trim(strtok((string)$this->url, '?') ?: '', '/ ') ?: null;

        if (!$this->url) {
            return;
        }

        if (str_contains($this->url, '//draft.')) {
            $this->addInvalidAttributeError('url');
            return;
        }

        $tenant = TenantCollection::getByUrl($this->url);

        if ($tenant && $tenant->id !== $this->id) {
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
                in_array($param, Yii::$app->getUrlManager()->getImmutableRuleParams(), true)
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
            str_starts_with((string)$this->cookie_domain, 'http')
            || !str_contains((string)$this->url, ltrim((string)$this->cookie_domain, '.'))
            || !preg_match('/^[a-z.]/', (string)$this->cookie_domain)
        ) {
            $this->addInvalidAttributeError('cookie_domain');
        }
    }

    #[Override]
    public function beforeSave($insert): bool
    {
        $this->setDefaultPosition();
        return parent::beforeSave($insert);
    }

    /**
     * @param array<string, mixed> $changedAttributes
     */
    #[Override]
    public function afterSave($insert, $changedAttributes): void
    {
        TenantCollection::invalidateCache();
        parent::afterSave($insert, $changedAttributes);
    }

    #[Override]
    public function beforeDelete(): bool
    {
        if (!parent::beforeDelete()) {
            if (!$this->hasErrors()) {
                $this->addError('id', Yii::t('tenant', 'TENANT_ERROR_DELETE_RELATION'));
            }

            return false;
        }

        if (self::find()->count() === 1) {
            $this->addError('id', Yii::t('tenant', 'TENANT_ERROR_DELETE_LAST'));
            return false;
        }

        return true;
    }

    #[Override]
    public function afterDelete(): void
    {
        TenantCollection::invalidateCache();
        parent::afterDelete();
    }

    public function isDeletable(): bool
    {
        return static::find()->count() > 1;
    }

    /**
     * @return TenantQuery<static>
     */
    #[Override]
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
        return $this->id ? ['/admin/tenant/tenant/update', 'id' => $this->id] : ['/admin/tenant/tenant/index'];
    }

    public function getCookieDomain(): ?string
    {
        return $this->cookie_domain ?? (($this->url ? parse_url($this->url, PHP_URL_HOST) : null) ?: null);
    }

    /**
     * A tenant without a URL pins no host: the URL manager keeps the one the request came in on, which is what an
     * installation that never wanted tenants runs on.
     */
    public function getHostInfo(): ?string
    {
        if (!$this->url) {
            return null;
        }

        if ($this->hostInfo === null) {
            $scheme = parse_url($this->url, PHP_URL_SCHEME);
            $this->hostInfo = ($scheme ? "$scheme://" : '//') . parse_url($this->url, PHP_URL_HOST);
        }

        return $this->hostInfo;
    }

    public function getMaxPosition(): int
    {
        return (int)self::find()->max('[[position]]');
    }

    public function getPathInfo(): string
    {
        $this->pathInfo ??= (string)(parse_url(trim((string)$this->url, '/'), PHP_URL_PATH) ?: '');
        return $this->pathInfo;
    }

    /**
     * @return list<string>
     * @noinspection PhpUnused
     */
    public function getTrailAttributes(): array
    {
        return array_values(array_diff($this->attributes(), [
            $this->getCustomAttributesColumn(),
            'position',
            'updated_by_user_id',
            'updated_at',
            'created_at',
        ]));
    }

    public function getAdminType(): string
    {
        return Yii::t('tenant', 'TENANT_NAME');
    }

    /**
     * @see \Hirtz\Skeleton\Widgets\Forms\Fields\SelectField::getItemsFromModel()
     * @return array<string, string>
     */
    public static function getLanguages(): array
    {
        $i18n = Yii::$app->getI18n();
        $languages = [];

        foreach ($i18n->getLanguages() as $language) {
            $languages[$language] = $i18n->getLabel($language);
        }

        return $languages;
    }

    protected function setDefaultPosition(): void
    {
        if (!$this->position) {
            $this->position = $this->position !== false ? ($this->getMaxPosition() + 1) : 0;
        }
    }

    /**
     * @return array<string, string>
     */
    #[Override]
    public function attributeHints(): array
    {
        return [
            ...parent::attributeHints(),
            'url' => Yii::t('tenant', 'TENANT_HINT_URL'),
        ];
    }

    #[Override]
    public function attributeLabels(): array
    {
        return [
            ...parent::attributeLabels(),
            'name' => Yii::t('tenant', 'TENANT_LABEL_NAME'),
            'url' => Yii::t('tenant', 'TENANT_LABEL_URL'),
            'language' => Yii::t('tenant', 'TENANT_LABEL_LANGUAGE'),
            'cookie_domain' => Yii::t('tenant', 'TENANT_LABEL_COOKIE_DOMAIN'),
            'position' => Yii::t('tenant', 'TENANT_LABEL_POSITION'),
        ];
    }

    #[Override]
    public static function tableName(): string
    {
        return '{{%tenant}}';
    }
}
