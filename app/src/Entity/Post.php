<?php
namespace App\Entity;

use App\Repository\PostRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\User;
use App\Entity\Topic;
use App\Entity\Comment;


#[ORM\Entity(repositoryClass: PostRepository::class)]
#[ORM\Table(name: 'posts')]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $pid = null;

    #[ORM\Column]
    private ?int $tid = null;

    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $postCreationTimestamp;

    #[ORM\Column(length: 512)]
    private string $title;

    #[ORM\Column(length: 8192)]
    private string $content;

    #[ORM\Column]
    private bool $isArchived = false;

    #[ORM\ManyToOne(inversedBy: 'userPosts')]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'posts')]
    #[ORM\JoinColumn(name: 'tid', referencedColumnName: 'tid', nullable: false)]
    private Topic $topic;

    /** @var Collection<int,Comment> */
    #[ORM\OneToMany(mappedBy: 'post', targetEntity: Comment::class, cascade: ['persist','remove'])]
    private ?Collection $comments = null;

    public function __construct()
    {
        $this->topicCreationTimestamp = new \DateTimeImmutable();
        $this->comments               = new ArrayCollection();
    }

    public function getPid(): ?int
    {
        return $this->pid;
    }

    public function getTid(): ?int
    {
        return $this->tid;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(User $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getPostCreationTimestamp(): ?\DateTimeImmutable
    {
        return $this->postCreationTimestamp;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(?string $content): static
    {
        $this->content = $content;

        return $this;
    }

    public function getIsArchived(): ?bool
    {
        return $this->isArchived;
    }

    public function setIsArchived(?bool $isArchived): static
    {
        $this->isArchived = $isArchived;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getTopic(): ?Topic
    {
        return $this->topic;
    }

    public function setTopic(Topic $topic): static
    {
        $this->topic = $topic;

        return $this;
    }

    public function getComments(): ?Collection
    {
        return $this->comments;
    }

}