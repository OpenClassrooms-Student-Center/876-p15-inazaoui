<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class MediaTest extends TestCase
{
    public function testMediaGettersAndSetters(): void
    {
        $media = new Media();

        $user = new User();
        $album = new Album();

        $media->setTitle('Photo test');
        $media->setPath('uploads/test.jpg');
        $media->setUser($user);
        $media->setAlbum($album);

        $this->assertSame('Photo test', $media->getTitle());
        $this->assertSame('uploads/test.jpg', $media->getPath());
        $this->assertSame($user, $media->getUser());
        $this->assertSame($album, $media->getAlbum());
    }

    public function testMediaInitialState(): void
    {
        $media = new Media();

        $this->assertNull($media->getId());
        $this->assertNull($media->getPath());
        $this->assertNull($media->getUser());
        $this->assertNull($media->getAlbum());
    }
}
