<?php

namespace App\Entity;

use App\Repository\UserSettingsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserSettingsRepository::class)]
class UserSettings
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column]
    private ?bool $display_email = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(User $id): static
    {
        $this->id = $id;

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
}
