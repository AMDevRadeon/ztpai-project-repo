<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
    private $userPasswordHasherInterface;

    public function __construct (UserPasswordHasherInterface $userPasswordHasherInterface) 
    {
        $this->userPasswordHasherInterface = $userPasswordHasherInterface;
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++)
        {
            $user = (new User())
                ->setNick("test_case_$i")
                ->setEmail("test_case_$i@email.com")
                ->setPasshash(
                    $this->userPasswordHasherInterface->hashPassword(new User(), "passwd$i")
                )
                ->setProvenance("place$i")
                ->setMotto("motto$i");

            $ur = (new UserRole())
                ->setRole(($i % 10 === 0) ? 2 : 1)
                ->setUser($user);
            

            $settings = (new UserSettings())
                ->setDisplayEmail(boolval($i % 2))
                ->setUser($user);
                
            $manager->persist($user);
            $manager->persist($ur);
            $manager->persist($settings);
        }

        $manager->flush();
    }
}
