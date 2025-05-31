<?php

namespace App\Database;

use App\Entity\Post;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class PostDatabaseQueries
{
    public static function getPosts(EntityManagerInterface $em, int $tid, int $offset, int $limit)
    {
        return $em->createQueryBuilder()
            ->select('t.pid', 't.uid', 't.postCreationTimestamp', 't.title', 't.content', 't.isArchived', 't.isClosed')
            ->from(Post::class, 't')
            ->where('t.tid = :current_tid')
            ->orderBy('t.postCreationTimestamp', 'DESC')
            ->setParameter('current_tid', $tid)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();
    }

    public static function getUsersFromPosts(EntityManagerInterface $em, array $posts)
    {
        $query_builder = $em->createQueryBuilder();
        $result_query_users = $query_builder
            ->select('u.uid', 'u.nick', 'u.email', 'u.accCreationTimestamp', 'u.provenance', 'u.motto', 'us.display_email')
            ->from(User::class, 'u')
            ->where($query_builder->expr()->in('u.uid', array_column($posts, 'uid')))
            ->join('u.settings', 'us')
            ->getQuery();

        return $result_query_users;
    }
}