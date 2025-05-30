<?php
namespace App\Entity;

use App\Repository\TopicRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\User;
use App\Entity\Post;


#[ORM\Entity(repositoryClass: TopicRepository::class)]
#[ORM\Table(name: 'topics')]
class Topic
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $tid = null;

    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $topicCreationTimestamp;

    #[ORM\Column(length: 512)]
    private string $title;

    #[ORM\Column(length: 8192)]
    private string $content;

    #[ORM\ManyToOne(inversedBy: 'userTopics')]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
    private User $user;

    /** @var Collection<int,Post> */
    #[ORM\OneToMany(mappedBy: 'topic', targetEntity: Post::class, cascade: ['persist','remove'])]
    private ?Collection $posts = null;

    public function __construct()
    {
        $this->topicCreationTimestamp = new \DateTimeImmutable();
        $this->posts                  = new ArrayCollection();
    }

    public function getTid(): ?int
    {
        return $this->tid;
    }

    public function getUid(): ?int
    {
        return $this->uid;
    }

    public function setUid(int $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function getTopicCreationTimestamp(): ?\DateTimeImmutable
    {
        return $this->topicCreationTimestamp;
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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPosts(): ?Collection
    {
        return $this->posts;
    }

}