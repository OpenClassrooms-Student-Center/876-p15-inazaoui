<?php

namespace App\Tests\Functional\Admin;

use App\Entity\User;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use App\Tests\BaseWebTestCase;
use Symfony\Component\HttpFoundation\Response;

class GuestPageTest extends BaseWebTestCase
{
    public function testGuestPageRequiresLogin(): void
    {
        $this->get('/admin/guests');
        $this->assertResponseRedirects('/login');  
    }

    public function testGuestPageRequiresAdmin(): void
    {
        $this->loginAs('activeGuest@test.com');
        $this->get('/admin/guests');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);
    }

    public function testGuestPageIndexRenders(): void
    {
        $this->loginAs('ina@zaoui.com');

        $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();

        $this->assertAnySelectorTextContains('a.nav-link', 'Invités');
        $this->assertAnySelectorTextContains('a.nav-link', 'Albums');
    }

    public function testGuestPageCanAddGuest(): void {
        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/guests');

        $addLink = $crawler->filter('a.btn[href="/admin/guest/add"]')->link();
        $crawler = $this->client->click($addLink);
        $this->assertResponseIsSuccessful();
        $this->assertSame('/admin/guest/add', $this->client->getRequest()->getPathInfo());

        $email = 'guesttest@test.com';
        $name = 'guest test';
        $description = "guest description test";

        $form = $crawler->selectButton('Ajouter')->form(
            [   'guest[name]' => $name,
                'guest[email]' => $email,
                'guest[description]' => $description,],
        );

        $this->client->submit($form);

        $this->assertResponseRedirects('/admin/guests');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();

        $this->assertAnySelectorTextContains(
            '.alert-success',
            "Invité ajouté avec succès"
        );
    }
       
    public function testGuestPageCanDisableGuest():void {
        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/guests');

        $this->assertResponseIsSuccessful();
        $blockFormNode = $crawler->filter('form[action^="/admin/guest/disable/"]');
        $this->assertGreaterThan(0, $blockFormNode->count(), "Pas d'invité à bloquer");

        $this->client->submit($blockFormNode->form());
        $this->assertResponseRedirects('/admin/guests');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }
    

    public function testGuestPageCanEnableGuest():void {
        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();
        
        $unblockFormNode = $crawler->filter('form[action^="/admin/guest/enable/"]');
        $this->assertGreaterThan(0, $unblockFormNode->count(), "Pas d'invité à débloquer");

        $this->client->submit($unblockFormNode->form());
        $this->assertResponseRedirects('/admin/guests');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful(); 
    }
       
    public function testGuestPageCanDeleteGuest(): void {
        
        $this->loginAs('ina@zaoui.com');
        $crawler = $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();
        $deleteFormNode = $crawler->filter('form[action^="/admin/guest/delete/"]');
        $this->assertGreaterThan(0, $deleteFormNode->count(), "Pas d'invité à supprimer");

        $this->client->submit($deleteFormNode->form());
        $this->assertResponseRedirects('/admin/guests');
        $this->client->followRedirect();
        $this->assertResponseIsSuccessful();
    }

    public function testSetPasswordPageRendersAndActivateGuests(){

        
        $container = static::getContainer();
        $em = $container->get(EntityManagerInterface::class);

        $guest = new User();
        $guest->setEmail('test@test.com');
        $guest->setName('test_set_password_activate_guest');
        $guest->setRoles(['ROLE_USER']);
        $guest->setIsActive(false);

        $token = bin2hex(random_bytes(32));
        $guest->setInvitationToken($token);
        $guest->setInvitationExpiredAt(new DateTimeImmutable('+2 days'));
        $em->persist($guest);
        $em->flush();

        $this->get('/set-password/'.$token);
        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('form');

        $this->client->submitForm('Activer ton compte', [
            'set_password[plainPassword][first]' => 'Password123@',
            'set_password[plainPassword][second]' => 'Password123@',
        ]);

        $this->assertResponseRedirects('/');
        $this->client->followRedirect();

        $em->clear(); // reload from DB

        $reloaded = $em->getRepository(User::class)->findOneBy(['email' => 'test@test.com']);

        $this->assertNotNull($reloaded);

        $this->assertTrue($reloaded->isActive());
        $this->assertNull($reloaded->getInvitationExpiredAt());
        $this->assertNull($reloaded->getInvitationToken());
    }


    public function testSetPasswordPageRedirectToHomePageWhenTokenIsInvalide(){
        
        $fakeToken = str_repeat('a', 64);
        $this->get('/set-password/'.$fakeToken);

        $this->assertResponseRedirects('/'); 
        $this->client->followRedirect();

        $this->assertSelectorExists('.alert-danger');
        $this->assertSelectorTextContains('.alert-danger', 'Invitation invalide ou expirée.');

    }

    public function testDisableGuestWithInvalidCsrfReturns403(): void {

        $this->loginAs('ina@zaoui.com');

        $crawler = $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();

        // disable the guest
        $firstBlockFormNode = $crawler->filter('form[action^="/admin/guest/disable/"]')->first();

        $actionInFirstBlockFormNode = $firstBlockFormNode->attr('action');

        $this->client->request('POST', $actionInFirstBlockFormNode, [
            '_token' => 'invalid-token'
        ]);

         $this->assertResponseStatusCodeSame(403);
    }

     public function testEnableGuestWithInvalidCsrfReturns403(): void {

        $this->loginAs('ina@zaoui.com');

        $crawler = $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();

        // enable the guest
        $firstEnableFormNode = $crawler->filter('form[action^="/admin/guest/enable/"]')->first();

        $actionInFirstEnableFormNode = $firstEnableFormNode->attr('action');

        $this->client->request('POST', $actionInFirstEnableFormNode, [
            '_token' => 'invalid-token'
        ]);

         $this->assertResponseStatusCodeSame(403);
    }

     public function testDeleteGuestWithInvalidCsrfReturns403(): void {

        $this->loginAs('ina@zaoui.com');

        $crawler = $this->get('/admin/guests');
        $this->assertResponseIsSuccessful();

        // delete the guest
        $firstDeleteFormNode = $crawler->filter('form[action^="/admin/guest/delete/"]')->first();

        $actionInFirstDeleteFormNode = $firstDeleteFormNode->attr('action');

        $this->client->request('POST', $actionInFirstDeleteFormNode, [
            '_token' => 'invalid-token'
        ]);

         $this->assertResponseStatusCodeSame(403);
    }  
}
