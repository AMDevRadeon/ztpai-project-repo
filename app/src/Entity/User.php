<?php
namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use App\Entity\Topic;
use App\Entity\Post;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[UniqueEntity('email')]
#[UniqueEntity('nick')]
#[ORM\Table(name: 'users')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    public const ROLE_MAP = [
        0 => 'ROLE_ANONYMOUS',
        1 => 'ROLE_USER',
        2 => 'ROLE_ADMIN',
    ];

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(length: 64)]
    private string $nick;

    #[ORM\Column(length: 180)]
    private string $email;

    #[ORM\Column]
    private string $passhash;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $provenance = null;

    #[ORM\Column(length: 128, nullable: true)]
    private ?string $motto = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $accCreationTimestamp;

    /** @var Collection<int,UserRole> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: UserRole::class, cascade: ['persist','remove'])]
    private Collection $userRoles;

    #[ORM\OneToOne(mappedBy: 'user', targetEntity: UserSettings::class, cascade: ['persist','remove'])]
    private ?UserSettings $settings = null;

    /** @var Collection<int,Topic> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Topic::class, cascade: ['persist','remove'])]
    private ?Collection $userTopics = null;

    /** @var Collection<int,Post> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Post::class, cascade: ['persist','remove'])]
    private ?Collection $userPosts = null;

    /** @var Collection<int,Comment> */
    #[ORM\OneToMany(mappedBy: 'user', targetEntity: Comment::class, cascade: ['persist','remove'])]
    private ?Collection $userComments = null;

    public function __construct()
    {
        $this->accCreationTimestamp = new \DateTimeImmutable();
        $this->userRoles            = new ArrayCollection();
        $this->userTopics           = new ArrayCollection();
        $this->userPosts            = new ArrayCollection();
        $this->userComments         = new ArrayCollection();
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function getNick(): ?string
    {
        return $this->nick;
    }

    public function setNick(string $nick): static
    {
        $this->nick = $nick;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPasshash(): ?string
    {
        return $this->passhash;
    }

    public function setPasshash(string $passhash): static
    {
        $this->passhash = $passhash;

        return $this;
    }

    public function getProvenance(): ?string
    {
        return $this->provenance;
    }

    public function setProvenance(?string $provenance): static
    {
        $this->provenance = $provenance;

        return $this;
    }

    public function getMotto(): ?string
    {
        return $this->motto;
    }

    public function setMotto(?string $motto): static
    {
        $this->motto = $motto;

        return $this;
    }

    public function getAccCreationTimestamp(): ?\DateTimeImmutable
    {
        return $this->accCreationTimestamp;
    }

    public function getRoles(): array
    {
        $roles = $this->userRoles->map(
            fn(UserRole $ur) => self::ROLE_MAP[$ur->getRole()]
        )->toArray();

        return $roles ?: ['ROLE_ANONYMOUS'];
    }

    public function getTopics(): ?Collection
    {
        return $this->userTopics;
    }

    public function getPosts(): ?Collection
    {
        return $this->userPosts;
    }

    public function getComments(): ?Collection
    {
        return $this->userComments;
    }

    public function getSettings(): UserSettings
    {
        return $this->settings;
    }

    public function getPassword(): string   
    {
        return $this->passhash;
    }

    public function getSalt(): ?string
    {
        return null;
    }

    public function eraseCredentials(): void
    {}

    public function getUserIdentifier(): string 
    { 
        return $this->email;
    }
}
