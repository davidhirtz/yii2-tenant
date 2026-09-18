<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Registry;

use Hirtz\Skeleton\Console\Application;
use Hirtz\Skeleton\Registry\Report as SkeletonReport;
use Hirtz\Tenant\Models\Collections\TenantCollection;
use Hirtz\Tenant\Models\Tenant;
use Hirtz\Tenant\Registry\Report;
use Hirtz\Tenant\Test\TestCase;
use Hirtz\Tenant\Test\Traits\TenantFixtureTrait;
use Yii;

/**
 * Under the console application, which is what a deploy pushes from and where the skeleton's own report has no
 * URL to answer with.
 */
class ReportTest extends TestCase
{
    use TenantFixtureTrait;

    protected string $applicationClass = Application::class;

    public function testTheBootstrapInstallsTheTenantReport(): void
    {
        self::assertInstanceOf(Report::class, SkeletonReport::create());
    }

    public function testTheDefaultTenantsHostIsTheUrl(): void
    {
        $report = SkeletonReport::create()->toArray();

        self::assertSame('https://www.domain.localhost', $report['url']);
    }

    public function testEveryTenantWithAUrlIsListed(): void
    {
        $tenants = SkeletonReport::create()->toArray()['extra']['tenants'];

        self::assertSame(
            ['https://www.domain.localhost', 'https://www.domain.localhost/de', 'https://www.draft.localhost'],
            array_column($tenants, 'url'),
        );

        self::assertSame(['name', 'url', 'status'], array_keys($tenants[0]));
        self::assertSame(Tenant::STATUS_DRAFT, $tenants[2]['status']);
    }

    public function testADefaultTenantWithoutAUrlLeavesItToTheNextOne(): void
    {
        Tenant::updateAll(['url' => null], ['id' => $this->getTenantFromFixture('default')->id]);
        TenantCollection::invalidateCache();

        $report = SkeletonReport::create()->toArray();

        // the German tenant's URL carries a path, and the host is what the report answers with
        self::assertSame('https://www.domain.localhost', $report['url']);
        self::assertCount(2, $report['extra']['tenants']);
    }

    public function testTheUrlOptionStillWins(): void
    {
        self::assertSame('https://www.example.com', SkeletonReport::create(['url' => 'https://www.example.com'])->toArray()['url']);
    }

    public function testExtraKeysOfTheCallerSurvive(): void
    {
        $extra = SkeletonReport::create(['extra' => ['build' => 42]])->toArray()['extra'];

        self::assertSame(42, $extra['build']);
        self::assertCount(3, $extra['tenants']);
    }

    public function testTenantsWithoutAUrlFallBackToTheSkeleton(): void
    {
        Tenant::updateAll(['url' => null]);
        TenantCollection::invalidateCache();

        $report = SkeletonReport::create()->toArray();

        self::assertNull($report['url']);
        self::assertArrayNotHasKey('extra', $report);
    }

    /**
     * The bootstrap only fills an empty slot, so a project's own report class is left alone.
     */
    public function testAProjectDefinitionWins(): void
    {
        Yii::$container->set(SkeletonReport::class, ProjectReport::class);
        $this->reloadApplication();

        self::assertInstanceOf(ProjectReport::class, SkeletonReport::create());
    }
}

class ProjectReport extends SkeletonReport
{
}
