<?php
namespace App\Entity;

use App\Repository\CommentRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use App\Entity\User;
use App\Entity\Post;

#[ORM\Entity(repositoryClass: CommentRepository::class)]
#[ORM\Table(name: 'comments')]
class Comment
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $cid = null;

    #[ORM\Column]
    private ?int $pid = null;

    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private \DateTimeImmutable $commentCreationTimestamp;

    #[ORM\Column(length: 16384)]
    private string $content;

    #[ORM\ManyToOne(inversedBy: 'userComments')]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
    private User $user;

    #[ORM\ManyToOne(inversedBy: 'comments')]
    #[ORM\JoinColumn(name: 'pid', referencedColumnName: 'pid', nullable: false)]
    private Post $post;

    public function __construct()
    {
        $this->topicCreationTimestamp = new \DateTimeImmutable();
    }


    public function getCid(): ?int
    {
        return $this->cid;
    }

    public function getPid(): ?int
    {
        return $this->pid;
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

    public function getCommentCreationTimestamp(): ?\DateTimeImmutable
    {
        return $this->commentCreationTimestamp;
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

    public function getPost(): ?Post
    {
        return $this->post;
    }

    public function setPost(Post $post): static
    {
        $this->post = $post;

        return $this;
    }
}