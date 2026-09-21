<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Migrations;

use Override;
use yii\db\Migration;

/**
 * @noinspection PhpUnused
 */
class M260101000100TenantBaseline extends Migration
{
    #[Override]
    public function safeUp(): void
    {
        $this->execute(
            <<<'SQL'
            CREATE TABLE `tenant` (
              `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
              `status` tinyint(3) NOT NULL DEFAULT 3,
              `name` varchar(255) NOT NULL,
              `url` varchar(100) DEFAULT NULL,
              `cookie_domain` varchar(255) DEFAULT NULL,
              `language` varchar(5) DEFAULT NULL,
              `custom_attributes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`custom_attributes`)),
              `entry_count` int(11) unsigned NOT NULL DEFAULT 0,
              `position` int(11) unsigned NOT NULL DEFAULT 0,
              `updated_by_user_id` int(11) unsigned DEFAULT NULL,
              `created_at` datetime NOT NULL,
              `updated_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `url` (`url`),
              KEY `tenant_updated_by_user_id` (`updated_by_user_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_unicode_ci
            SQL
        );

        $this->execute(
            <<<'SQL'
            ALTER TABLE `tenant` ADD CONSTRAINT `tenant_updated_by_user_id` FOREIGN KEY (`updated_by_user_id`) REFERENCES `user` (`id`) ON DELETE SET NULL
            SQL
        );

        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item` (`name`, `type`, `description`, `rule_name`, `data`, `updated_at`, `created_at`) VALUES
              ('tenant', '2', '{\"category\":\"tenant\",\"key\":\"AUTH_TENANT_DESCRIPTION\"}', NULL, NULL, '1789985581', '1789985581')
            SQL
        );

        $this->execute(
            <<<'SQL'
            INSERT INTO `auth_item_child` (`parent`, `child`) VALUES
              ('admin', 'tenant')
            SQL
        );
    }

    #[Override]
    public function safeDown(): bool
    {
        echo "    > a baseline cannot be reverted, restore a dump instead\n";
        return false;
    }
}
