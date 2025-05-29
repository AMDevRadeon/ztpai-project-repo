<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\User;


#[ORM\Entity]
#[ORM\Table(name: 'users_settings')]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $uid = null;

    #[ORM\Column(options: ['default' => false])]
    private bool $display_email = false;

    #[ORM\OneToOne(inversedBy: 'settings')]
    #[ORM\JoinColumn(name: 'uid', referencedColumnName: 'uid', nullable: false)]
    private User $user;

    public function getUid(): ?int
    {
        return $this->id;
    }

    public function setUid(User $uid): static
    {
        $this->uid = $uid;

        return $this;
    }

    public function isDisplayEmail(): ?bool
    {
        return $this->display_email;
    }

    public function setDisplayEmail(bool $display_email): static
    {
        $this->display_email = $display_email;

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
}
