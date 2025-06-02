<?php

namespace App\DataFixtures;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;

use App\Entity\Topic;
use App\Entity\Post;
use App\Entity\Comment;

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
        $user_count = 100;
        $useful_users = [];

        for ($i = 0; $i < $user_count; $i++)
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

            if ($i < $user_count)
            {
                $useful_users[$i] = $user;
            }
                
            $manager->persist($user);
            $manager->persist($ur);
            $manager->persist($settings);
        }

        $manager->flush();

        $topic_not_archived = (new Topic())
            ->setUid($useful_users[10]->getUid())
            ->setTitle("not_archived_topic")
            ->setContent("case_isArchived_false")
            ->setIsArchived(false)
            ->setUser($useful_users[10]);

        $topic_archived = (new Topic())
            ->setUid($useful_users[20]->getUid())
            ->setTitle("archived_topic")
            ->setContent("case_isArchived_true")
            ->setIsArchived(true)
            ->setUser($useful_users[20]);

        $manager->persist($topic_not_archived);
        $manager->persist($topic_archived);

        $manager->flush();

        for ($i = 0; $i < 10; $i++)
        {
            $closed = ($i % 2 === 0);
            $archived = ($i % 3 === 0);
            $post_a = (new Post())
                ->setUid($useful_users[$i]->getUid())
                ->setTid($topic_not_archived->getTid())
                ->setTitle("post_$i")
                ->setContent("case_"
                             . (($closed) ? "closed" : "x")
                             . (($archived) ? "archived" : "x"))
                ->setIsClosed($closed)
                ->setIsArchived($archived)
                ->setTopic($topic_not_archived)
                ->setUser($useful_users[$i]);

            $post_b = (new Post())
                ->setUid($useful_users[$i]->getUid())
                ->setTid($topic_archived->getTid())
                ->setTitle("post_$i")
                ->setContent("case_"
                             . (($closed) ? "closed" : "x")
                             . (($archived) ? "archived" : "x"))
                ->setIsClosed($closed)
                ->setIsArchived($archived)
                ->setTopic($topic_archived)
                ->setUser($useful_users[$i]);
        
            $manager->persist($post_a);
            $manager->persist($post_b);

            $manager->flush();

            for ($j = 10; $j < 50; $j++)
            {
                $comment_a = (new Comment())
                    ->setUid($useful_users[$j]->getUid())
                    ->setPid($post_a->getPid())
                    ->setContent("comment_$j")
                    ->setPost($post_a)
                    ->setUser($useful_users[$j]);

                $comment_b = (new Comment())
                    ->setUid($useful_users[$j]->getUid())
                    ->setPid($post_b->getPid())
                    ->setContent("comment_$j")
                    ->setPost($post_b)
                    ->setUser($useful_users[$j]);

                $manager->persist($comment_a);
                $manager->persist($comment_b);
            }
        }

        $manager->flush();
    }
}
