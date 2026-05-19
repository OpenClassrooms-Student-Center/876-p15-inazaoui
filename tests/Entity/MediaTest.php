<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Media.
 *
 * Vérifie le bon fonctionnement des accesseurs et mutateurs de l'entité,
 * ainsi que la gestion des associations optionnelles (album).
 */
class MediaTest extends TestCase
{
    /**
     * Vérifie que le titre, le chemin, l'utilisateur et l'album
     * peuvent être définis et récupérés correctement.
     */
    public function testGettersAndSetters(): void
    {
        $media = new Media();
        $user = new User();
        $album = new Album();

        $media->setTitle('Photo test');
        $media->setPath('uploads/test.jpg');
        $media->setUser($user);
        $media->setAlbum($album);

        $this->assertEquals('Photo test', $media->getTitle());
        $this->assertEquals('uploads/test.jpg', $media->getPath());
        $this->assertSame($user, $media->getUser());
        $this->assertSame($album, $media->getAlbum());
    }

    /**
     * Vérifie que l'album peut être défini à null (association optionnelle).
     */
    public function testNullableAlbum(): void
    {
        $media = new Media();
        $media->setAlbum(null);

        $this->assertNull($media->getAlbum());
    }
}