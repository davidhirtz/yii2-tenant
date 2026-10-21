<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Override;
use Yii;
use yii\db\JsonExpression;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M261021100000TenantLanguages extends Migration
{
    #[Override]
    public function safeUp(): void
    {
        $this->addColumn('{{%tenant}}', 'languages', (string)$this->json()->null()->after('language'));

        // Without `i18nUrl` a fixed language was the only one a visitor reached; `null` would now offer them all
        if (!Yii::$app->getUrlManager()->i18nUrl) {
            $this->update('{{%tenant}}', ['languages' => new JsonExpression([])], ['not', ['language' => null]]);
        }
    }

    #[Override]
    public function safeDown(): void
    {
        $this->dropColumn('{{%tenant}}', 'languages');
    }
}
