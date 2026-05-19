<?php

namespace App\Entity;

use App\Repository\AlbumRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * Entité représentant un album photo.
 *
 * Un album regroupe des médias et peut être associé à un ou plusieurs utilisateurs.
 */
#[ORM\Entity(repositoryClass: AlbumRepository::class)]
class Album
{
    /** @var int|null Identifiant unique de l'album, généré automatiquement */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var string Nom de l'album */
    #[ORM\Column]
    private string $name;

    /**
     * Retourne l'identifiant de l'album.
     *
     * @return int|null L'identifiant, ou null si l'entité n'est pas encore persistée
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne le nom de l'album.
     *
     * @return string Le nom de l'album
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Définit le nom de l'album.
     *
     * @param string $name Le nouveau nom de l'album
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }
}
