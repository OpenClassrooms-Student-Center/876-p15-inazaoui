<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * Entité représentant un utilisateur de l'application.
 *
 * Un utilisateur peut être administrateur (photographe principal) ou invité (photographe secondaire).
 * Les invités peuvent être bloqués par l'administrateur pour leur interdire l'accès.
 */
#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    /** @var int|null Identifiant unique de l'utilisateur, généré automatiquement */
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    /** @var bool Indique si l'utilisateur est administrateur */
    #[ORM\Column]
    private bool $admin = false;

    /** @var string Nom d'affichage de l'utilisateur */
    #[ORM\Column(length: 255)]
    private string $name = '';

    /** @var string|null Description ou biographie de l'utilisateur */
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description;

    /** @var string Adresse e-mail unique utilisée comme identifiant de connexion */
    #[ORM\Column(length: 180, unique: true)]
    private string $email = '';

    /** @var string Mot de passe haché de l'utilisateur */
    #[ORM\Column]
    private string $password = '';

    /** @var Collection<int, Media> Collection des médias appartenant à cet utilisateur */
    #[ORM\OneToMany(targetEntity: Media::class, mappedBy: 'user')]
    private Collection $medias;

    /** @var bool Indique si le compte est bloqué par l'administrateur */
    #[ORM\Column(options: ['default' => false])]
    private bool $blocked = false;

    /**
     * Initialise la collection de médias de l'utilisateur.
     */
    public function __construct()
    {
        $this->medias = new ArrayCollection();
    }

    /**
     * Retourne l'identifiant de l'utilisateur.
     *
     * @return int|null L'identifiant, ou null si l'entité n'est pas encore persistée
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Retourne l'adresse e-mail de l'utilisateur.
     *
     * @return string|null L'adresse e-mail, ou null si non définie
     */
    public function getEmail(): ?string
    {
        return $this->email;
    }

    /**
     * Définit l'adresse e-mail de l'utilisateur.
     *
     * @param string $email La nouvelle adresse e-mail
     * @return static L'instance courante (fluent interface)
     */
    public function setEmail(string $email): static
    {
        $this->email = $email;
        return $this;
    }

    /**
     * Retourne l'identifiant unique de l'utilisateur pour Symfony Security (l'e-mail).
     *
     * @return string L'adresse e-mail de l'utilisateur
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * Retourne les rôles de l'utilisateur.
     * Les administrateurs reçoivent ROLE_ADMIN et ROLE_USER ; les invités reçoivent uniquement ROLE_USER.
     *
     * @return string[] La liste des rôles
     */
    public function getRoles(): array
    {
        return $this->admin ? ['ROLE_ADMIN', 'ROLE_USER'] : ['ROLE_USER'];
    }

    /**
     * Retourne le mot de passe haché de l'utilisateur.
     *
     * @return string Le mot de passe haché
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    /**
     * Définit le mot de passe haché de l'utilisateur.
     *
     * @param string $password Le mot de passe haché
     * @return static L'instance courante (fluent interface)
     */
    public function setPassword(string $password): static
    {
        $this->password = $password;
        return $this;
    }

    /**
     * Indique si le compte de l'utilisateur est bloqué.
     *
     * @return bool true si le compte est bloqué, false sinon
     */
    public function isBlocked(): bool
    {
        return $this->blocked;
    }

    /**
     * Efface les données sensibles temporaires de l'utilisateur.
     * Requis par l'interface UserInterface ; aucune donnée temporaire à effacer ici.
     */
    public function eraseCredentials(): void {}

    /**
     * Retourne le nom d'affichage de l'utilisateur.
     *
     * @return string|null Le nom, ou null si non défini
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * Définit le nom d'affichage de l'utilisateur.
     *
     * @param string|null $name Le nouveau nom
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * Retourne la description ou biographie de l'utilisateur.
     *
     * @return string|null La description, ou null si non définie
     */
    public function getDescription(): ?string
    {
        return $this->description;
    }

    /**
     * Définit la description ou biographie de l'utilisateur.
     *
     * @param string|null $description La nouvelle description
     */
    public function setDescription(?string $description): void
    {
        $this->description = $description;
    }

    /**
     * Retourne la collection des médias de l'utilisateur.
     *
     * @return Collection<int, Media> La collection des médias
     */
    public function getMedias(): Collection
    {
        return $this->medias;
    }

    /**
     * Remplace la collection des médias de l'utilisateur.
     *
     * @param Collection<int, Media> $medias La nouvelle collection de médias
     */
    public function setMedias(Collection $medias): void
    {
        $this->medias = $medias;
    }

    /**
     * Indique si l'utilisateur est administrateur.
     *
     * @return bool true si l'utilisateur est administrateur, false sinon
     */
    public function isAdmin(): bool
    {
        return $this->admin;
    }

    /**
     * Définit le statut administrateur de l'utilisateur.
     *
     * @param bool $admin true pour accorder les droits administrateur
     */
    public function setAdmin(bool $admin): void
    {
        $this->admin = $admin;
    }

    /**
     * Définit le statut de blocage du compte.
     *
     * @param bool $blocked true pour bloquer le compte, false pour le débloquer
     */
    public function setBlocked(bool $blocked): void
    {
        $this->blocked = $blocked;
    }
}
