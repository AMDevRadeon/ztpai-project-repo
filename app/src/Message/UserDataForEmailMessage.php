<?php
 
namespace App\Message;

use Symfony\Component\Messenger\Attribute\AsMessage;

#[AsMessage('async')]
class UserDataForEmailMessage
{
    private string $nick;
    private string $email;

    public function __construct(string $nick, string $email)
    {
        $this->nick = $nick;
        $this->email = $email;
    }

    public function getNick(): string
    {
        return $this->nick;
    }

    public function getEmail(): string
    {
        return $this->email;
    }
}