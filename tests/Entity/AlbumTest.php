<?php

namespace App\Tests\Entity;

use App\Entity\Album;
use PHPUnit\Framework\TestCase;

/**
 * Tests unitaires de l'entité Album.
 *
 * Vérifie le bon fonctionnement des accesseurs et mutateurs de l'entité.
 */
class AlbumTest extends TestCase
{
    /**
     * Vérifie que le nom peut être défini et récupéré correctement,
     * et que l'identifiant est null avant la persistance.
     */
    public function testGettersAndSetters(): void
    {
        $album = new Album();
        $album->setName('Album Test');

        $this->assertEquals('Album Test', $album->getName());
        $this->assertNull($album->getId());
    }
}