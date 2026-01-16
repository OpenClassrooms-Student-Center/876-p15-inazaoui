<?php

declare(strict_types=1);

namespace App\Tests\Functional\Admin;

use App\Tests\Support\TestUserFactory;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class AdminDashboardTest extends WebTestCase
{
    public function testAdminDashboardLoads(): void
    {
        $client = static::createClient();
        $em = static::getContainer()->get(EntityManagerInterface::class);

        $admin = TestUserFactory::getOrCreateIna($em);
        $client->loginUser($admin);

        $client->request('GET', '/admin');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('h1');
    }
}
