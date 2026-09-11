<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests;

use Hirtz\Tenant\Module;
use Hirtz\Tenant\Test\TestCase;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Yii;

final class ModuleTest extends TestCase
{
    #[DataProvider('enableAdminModuleDataProvider')]
    public function testAdminModuleFollowsTheOption(bool $enableAdminModule): void
    {
        self::assertInstanceOf(Module::class, Yii::$app->getModule('tenant'));

        self::assertSame(
            $enableAdminModule,
            Yii::$app->getModule('admin')->getModule('tenant') !== null
        );
    }

    /**
     * @return array<string, array{bool}>
     */
    public static function enableAdminModuleDataProvider(): array
    {
        return [
            'enabled' => [true],
            'disabled' => [false],
        ];
    }

    #[Override]
    protected function setUp(): void
    {
        [$enableAdminModule] = $this->providedData();

        $this->config = [
            ...require(__DIR__ . '/../config/test.php'),
            'modules' => [
                'tenant' => [
                    'enableAdminModule' => $enableAdminModule,
                ],
            ],
        ];

        parent::setUp();
    }
}
