<?php

namespace App\Tests;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

abstract class BaseWebTestCase extends WebTestCase
{

    protected KernelBrowser $client;

    protected function setup(): void
    {
        parent::setUp();
        $this->client = static::createClient();
    }

    protected function get($uri): Crawler
    {
        return $this->client->request('GET', $uri);
    }

    protected function loginAs(string $email): void
    {

        $repo = static::getContainer()->get(UserRepository::class);
        $user = $repo->findOneBy(['email' => $email]);
        self::assertNotNull($user, "Pas d'utilisateur: $email");

        $this->client->loginUser($user);
    }

    protected function assertPageRequiresLogin(string $uri): void
    {
        $this->get($uri);
        $this->assertResponseRedirects('/login');
    }
}
