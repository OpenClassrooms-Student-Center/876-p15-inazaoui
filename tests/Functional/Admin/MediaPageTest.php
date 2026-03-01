<?php

namespace App\Tests\Functional\Admin;

use App\Entity\Album;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use App\Tests\BaseWebTestCase;

class MediaPageTest extends BaseWebTestCase
{
    public function testMediaPageRequiresLogin(): void
    {
        $this->get('/admin/media');
        $this->assertResponseRedirects('/login');
    }

    public function testMediaPageIndexRendersAsAdmin()
    {

        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/media');

        $this->assertResponseIsSuccessful();

        $this->assertAnySelectorTextContains('a.nav-link', 'Invités');
        $this->assertAnySelectorTextContains('a.nav-link', 'Albums');
    }

    public function testMediaPageCanAddMediaAsAdmin()
    {

        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/media');

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $album = $em->getRepository(Album::class)->findOneBy([]);

        $guestRepo = static::getContainer()->get(UserRepository::class);
        $admin = $guestRepo->findOneBy(['email' => 'ina@zaoui.com']);
        $adminId = (string) $admin->getId();

        // add the media 
        $addLink = $crawler->filter('a.btn[href="/admin/media/add"]')->link();
        $this->client->click($addLink);
        $this->assertResponseIsSuccessful();
        $this->assertSame('/admin/media/add', $this->client->getRequest()->getPathInfo());

        $fixtureImagePath = 'src/DataFixtures/imageFixtures/test.jpg';

        $uploadedFile = new UploadedFile($fixtureImagePath, 'test.jpg', 'image/jpeg', null, true);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $album = $em->getRepository(Album::class)->findOneBy([]);

        $this->client->submitForm(
            'Ajouter',
            [
                'media[user]' => $adminId,
                'media[title]' => 'My admin test add media',
                'media[file]' => $uploadedFile,
                'media[album]' => $album->getId(),
            ]
        );

        $this->assertResponseRedirects('/admin/media');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testMediaPageIndexRendersAsActiveGuest()
    {
        $this->loginAs('activeGuest@test.com');

        $this->get('/admin/media');
        $this->assertResponseIsSuccessful();

        // no "invite" and "album" in the nav bar as guest
        $this->assertAnySelectorTextNotContains('a.nav-link', 'Invités');
        $this->assertAnySelectorTextNotContains('a.nav-link', 'Albums');
    }

    public function testMediaPageCanAddMediaAsActiveGuest(): void
    {

        $this->loginAs('activeGuest@test.com');

        $crawler = $this->get('/admin/media');

        $addLink = $crawler->filter('a.btn[href="/admin/media/add"]')->link();
        $this->client->click($addLink);
        $this->assertResponseIsSuccessful();
        $this->assertSame('/admin/media/add', $this->client->getRequest()->getPathInfo());

        $fixtureImagePath = 'src/DataFixtures/imageFixtures/test.jpg';

        $uploadedFile = new UploadedFile($fixtureImagePath, 'test.jpg', 'image/jpeg', null, true);

        $em = static::getContainer()->get(EntityManagerInterface::class);
        $album = $em->getRepository(Album::class)->findOneBy([]);

        $this->client->submitForm(
            'Ajouter',
            [
                'media[title]' => 'My test add media',
                'media[file]' => $uploadedFile,
                'media[album]' => $album->getId(),
            ]
        );

        $this->assertResponseRedirects('/admin/media');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testMediaPageCanDeleteMedia(): void
    {

        $this->loginAs('activeGuest@test.com');

        $crawler = $this->get('/admin/media');

        $deleteFormNode = $crawler->filter('form[action^="/admin/media/delete/"]');

        $this->client->submit($deleteFormNode->form());
        $this->assertResponseRedirects('/admin/media');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteMediaWithInvalidCsrfReturns403(): void
    {

        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/media');
        $this->assertResponseIsSuccessful();

        // delete the guest
        $firstDeleteFormNode = $crawler->filter('form[action^="/admin/media/delete/"]')->first();

        $actionInFirstDeleteFormNode = $firstDeleteFormNode->attr('action');

        $this->client->request('POST', $actionInFirstDeleteFormNode, [
            '_token' => 'invalid-token'
        ]);

        $this->assertResponseStatusCodeSame(403);
    }
}
