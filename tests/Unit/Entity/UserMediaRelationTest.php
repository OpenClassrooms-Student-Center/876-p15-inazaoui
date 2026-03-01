<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Media;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserMediaRelationTest extends TestCase
{
    public function testAddMediaAddsToCollectionAndSetsOwningSide(): void
    {
        $user = new User();
        $media = new Media();

        $user->addMedia($media);

        $this->assertTrue($user->getMedias()->contains($media));
        $this->assertSame($user, $media->getUser());
    }

    public function testRemoveMediaRemovesFromCollectionAndUnsetsOwningSide(): void
    {
        $user = new User();
        $media = new Media();

        $user->addMedia($media);
        $user->removeMedia($media);
        $this->assertFalse($user->getMedias()->contains($media));
        $this->assertNull($media->getUser());
    }

    

}
