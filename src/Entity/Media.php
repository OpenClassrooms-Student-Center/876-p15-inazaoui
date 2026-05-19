<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Entité représentant un média (photo) dans l'application.
 *
 * Un média est lié à un utilisateur et peut optionnellement appartenir à un album.
 * Le fichier physique est stocké dans le dossier uploads/.
 */
#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    /** @var int|null Identifiant unique du média, généré automatiquement */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var User|null Utilisateur propriétaire du média */
    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: "medias", fetch: "EAGER")]
    private ?User $user = null;

    /** @var Album|null Album auquel le média est rattaché (optionnel) */
    #[ORM\ManyToOne(targetEntity: Album::class, fetch: "EAGER")]
    private ?Album $album = null;

    /** @var string Chemin relatif vers le fichier image sur le serveur */
    #[ORM\Column]
    private string $path;

    /** @var string Titre du média affiché dans l'interface */
    #[ORM\Column]
    private string $title;

    /** @var UploadedFile|null Fichier uploadé (non persisté en base, utilisé uniquement lors du formulaire) */
    private ?UploadedFile $file = null;

    /**
     * Retourne l'identifiant du média.
     *
     * @return int|null L'identifiant, ou null si l'entité n'est pas encore persistée
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'utilisateur propriétaire du média.
     *
     * @return User|null L'utilisateur propriétaire, ou null si non défini
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * Définit l'utilisateur propriétaire du média.
     *
     * @param User|null $user L'utilisateur à associer au média
     */
    public function setUser(?User $user): void
    {
        $this->user = $user;
    }

    /**
     * Retourne le chemin relatif du fichier image.
     *
     * @return string Le chemin du fichier
     */
    public function getPath(): string
    {
        return $this->path;
    }

    /**
     * Définit le chemin relatif du fichier image.
     *
     * @param string $path Le chemin du fichier
     */
    public function setPath(string $path): void
    {
        $this->path = $path;
    }

    /**
     * Retourne le titre du média.
     *
     * @return string Le titre du média
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Définit le titre du média.
     *
     * @param string $title Le nouveau titre
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Retourne le fichier uploadé (disponible uniquement lors du traitement du formulaire).
     *
     * @return UploadedFile|null Le fichier uploadé, ou null si aucun
     */
    public function getFile(): ?UploadedFile
    {
        return $this->file;
    }

    /**
     * Définit le fichier uploadé à traiter.
     *
     * @param UploadedFile|null $file Le fichier uploadé
     */
    public function setFile(?UploadedFile $file): void
    {
        $this->file = $file;
    }

    /**
     * Retourne l'album associé au média.
     *
     * @return Album|null L'album, ou null si le média n'appartient à aucun album
     */
    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    /**
     * Définit l'album associé au média.
     *
     * @param Album|null $album L'album à associer, ou null pour en retirer l'association
     */
    public function setAlbum(?Album $album): void
    {
        $this->album = $album;
    }
}
