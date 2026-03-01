<?php

namespace App\Tests\Functional\Front;

use App\Repository\UserRepository;
use App\Tests\BaseWebTestCase;

final class GuestspageTest extends BaseWebTestCase
{

    public function testGuestsPageListsGuestsWithDiscoverLinks(): void
    {
        // open guests list
        $crawler = $this->get('/guests');
        $this->assertResponseIsSuccessful();

        // assert the page title
        $this->assertSelectorTextContains('h3', 'Invités');

        $this->assertGreaterThan(0, $crawler->filter('.guests .guest')->count());

        // title (character + space + digital)
        $firstGuestTitle = trim($crawler->filter('.guests .guest h4')->first()->text());
        $this->assertMatchesRegularExpression('/^.+\s\(\d+\)$/', $firstGuestTitle);

        $firstLink = $crawler
            ->filter('.guests .guest a:contains("découvrir")')
            ->first()
            ->link();

        $firstHref = $firstLink->getUri();

        $this->assertStringContainsString('/guest/', $firstHref);

        // open the discover link
        $this->client->click($firstLink);
        $this->assertResponseIsSuccessful();

        $this->assertMatchesRegularExpression('#^/guest/\d+$#', $this->client->getRequest()->getPathInfo());
    }

    public function testGuestPagesDoesNotShowBlockedGuests()
    {
        $this->loginAs('blockedGuest@test.com');

        /** @var UserRepository $usersRepo */
        $usersRepo = static::getContainer()->get(UserRepository::class);
        $blockedGuest = $usersRepo->findOneBy(['email' => 'blockedGuest@test.com']);
        $idOfBlockedGuest = $blockedGuest->getId();

        $crawler = $this->get('/guests');
        $this->assertResponseIsSuccessful();

        $selector = sprintf('.guest .guest[data-guest-id="%d"]', $idOfBlockedGuest);

        $this->assertCount(0, $crawler->filter($selector));
    }
}
