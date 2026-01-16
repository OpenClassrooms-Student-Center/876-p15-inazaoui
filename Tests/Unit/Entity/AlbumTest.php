<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use App\Entity\Media;
use PHPUnit\Framework\TestCase;

class AlbumTest extends TestCase
{
    public function testGettersAndSetters(): void
    {
        $album = new Album();

        $album->setName('Album Test');

        $this->assertNull($album->getId());
        $this->assertSame('Album Test', $album->getName());
    }

    public function testMediaCollectionIsInitialized(): void
    {
        $album = new Album();

        $this->assertCount(0, $album->getMedia());
    }

    public function testAddMedia(): void
    {
        $album = new Album();
        $media = new Media();

        $album->addMedia($media);

        $this->assertCount(1, $album->getMedia());
        $this->assertTrue($album->getMedia()->contains($media));
        $this->assertSame($album, $media->getAlbum());
    }

    public function testRemoveMedia(): void
    {
        $album = new Album();
        $media = new Media();

        $album->addMedia($media);
        $album->removeMedia($media);

        $this->assertCount(0, $album->getMedia());
        $this->assertNull($media->getAlbum());
    }

    public function testAlbumNameAndMediaManagement(): void
    {
        $album = new Album();
        $media = new Media();

        // Test du setter / getter name
        $album->setName('Vacances 2024');
        $this->assertSame('Vacances 2024', $album->getName());

        // Test relation addMedia
        $album->addMedia($media);
        $this->assertCount(1, $album->getMedia());
        $this->assertSame($album, $media->getAlbum());

        // Test relation removeMedia
        $album->removeMedia($media);
        $this->assertCount(0, $album->getMedia());
        $this->assertNull($media->getAlbum());
    }
}
