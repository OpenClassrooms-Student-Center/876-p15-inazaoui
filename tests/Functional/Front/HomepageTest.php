<?php

namespace App\Tests\Functional\Front;

use App\Tests\BaseWebTestCase;

final class HomepageTest extends BaseWebTestCase
{

    public function testHomePageHasPublicNavAndDiscoverLinkRedirectToLoginPage():void
    {
        $crawler = $this->get('/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('nav');

        $this->assertSelectorExists('nav ul a:contains("Invité")');
        $this->assertSelectorExists('nav ul a:contains("Portfolio")');
        $this->assertSelectorExists('nav ul a:contains("Qui suis-je ?")');
        $this->assertSelectorExists('nav ul a:contains("Connexion")');
        $this->assertSelectorExists('a:contains("découvrir")');
        // Dashboard should not be displayed
        $this->assertSelectorNotExists('nav a:contains("Dashboard")');
  
       $link = $crawler->filter('[data-test-id="discover-link"]')->link();
      
        $this->client->click($link);
        $this->assertResponseRedirects('/login');
        
    }

     public function testHomePageLoggedGuestHasDashboardLinkAndRedirectToDiscoverPage():void 
    {
        $this->loginAs('activeGuest@test.com');
        $crawler = $this->get('/');
        $this->assertResponseIsSuccessful();

        $this->assertSelectorExists('nav');
        $this->assertSelectorExists('nav a:contains("Dashboard")');
        $this->assertSelectorExists('nav a:contains("Déconnexion")');

        $link = $crawler->filter('[data-test-id="discover-link"]')->link();
        $this->client->click($link);
        $this->assertResponseIsSuccessful();
        $this->assertSame('/portfolio', $this->client->getRequest()->getPathInfo());
    }

}
