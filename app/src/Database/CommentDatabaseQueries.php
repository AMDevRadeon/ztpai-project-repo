<?php

namespace App\Database;

use App\Entity\Comment;
use Doctrine\ORM\EntityManagerInterface;

class CommentDatabaseQueries
{
    public static function getComments(EntityManagerInterface $em, int $pid, int $offset, int $limit)
    {
        return $em->createQueryBuilder()
            ->select('t.cid', 't.uid', 't.commentCreationTimestamp', 't.content')
            ->from(Comment::class, 't')
            ->where('t.pid = :current_pid')
            ->orderBy('t.commentCreationTimestamp', 'DESC')
            ->setParameter('current_pid', $pid)
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();
    }
}