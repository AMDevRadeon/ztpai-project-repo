<?php

namespace App\Entity;

use JsonSerializable;
use App\Repository\UserRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: '`user`')]
class User implements JsonSerializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'uid')]
    private ?int $id = null;

    #[ORM\Column(length: 64)]
    private ?string $nick = null;

    #[ORM\Column(length: 255)]
    private ?string $email = null;

    #[ORM\Column(length: 184)]
    private ?string $passhash = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $provenance = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $motto = null;

    // #[ORM\Column(name: 'user_creation_timestamp', type: Types::DATETIMETZ_IMMUTABLE, selectable: false, updatable: false, insertable: false)]
    // private ?\DateTimeImmutable $account_creation_timestamp = null;

    public function getId(): ?int
    {
        return $this->id;
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

    public function jsonSerialize()
    {
        return array (
            'nick' => $this->getNick(),
            'email' => $this->getEmail(),
            'provenance' => $this->getProvenance(),
            'motto' => $this->getMotto()
        );
    }

    // public function getAccountCreationTimestamp(): ?\DateTimeImmutable
    // {
    //     return $this->account_creation_timestamp;
    // }

    // public function setAccountCreationTimestamp(\DateTimeImmutable $account_creation_timestamp): static
    // {
    //     $this->account_creation_timestamp = $account_creation_timestamp;

    //     return $this;
    // }
}
