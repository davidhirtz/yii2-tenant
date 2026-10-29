<?php

declare(strict_types=1);

namespace Hirtz\Tenant\Tests\Functional;

use Hirtz\Skeleton\Test\Browser;
use Hirtz\Tenant\Test\Fixtures\TenantFixture;
use Hirtz\Tenant\Test\TestCase;
use Override;

final class AdminDraftRedirectTest extends TestCase
{
    /**
     * @return array<string, mixed>
     */
    #[Override]
    public function fixtures(): array
    {
        return [
            'tenant' => [
                'class' => TenantFixture::class,
            ],
        ];
    }

    public function testTheDraftHostRedirectsToTheAdminOfATenantWithAPath(): void
    {
        $client = new Browser(['HTTP_HOST' => 'draft.domain.localhost', 'HTTPS' => 'on']);
        $client->followRedirects(false);
        $client->request('GET', 'https://draft.domain.localhost/de/admin/user/update?id=1');

        self::assertSame(
            'https://www.domain.localhost/de/admin/user/update?id=1',
            $client->getInternalResponse()->getHeader('location'),
        );
    }
}
