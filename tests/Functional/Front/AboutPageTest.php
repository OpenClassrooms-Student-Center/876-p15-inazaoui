<?php

namespace App\Tests\Functional\Front;

use App\Tests\BaseWebTestCase;

class AboutPageTest extends BaseWebTestCase
{
    public function testAboutPageIsSuccessfulAndShowContents(): void
    {
        $crawler = $this->get('/about');

        $this->assertResponseIsSuccessful();

        $this->assertSelectorTextContains('h2.about-title', 'Qui suis-je ?');

        $this->assertSelectorExists('img.about-img[alt="Ina Zaoui"]');

        $imgSrc = $crawler->filter('img.about-img')->attr('src');

        $this->assertStringContainsString('/images/ina.png', $imgSrc);

        $this->assertSelectorTextContains('.about-description', "Chaque cliché d'Ina Zaoui est une ode à la beauté brute et à la fragilité de notre planète");
    }
}
