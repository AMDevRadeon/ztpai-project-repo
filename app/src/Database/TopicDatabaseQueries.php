<?php

namespace App\Database;

use App\Entity\Topic;
use Doctrine\ORM\EntityManagerInterface;

class TopicDatabaseQueries
{
    public static function getTopics(EntityManagerInterface $em, int $offset, int $limit)
    {
        // Maybe count numer of posts in a topic?
        return $em->createQueryBuilder()
            ->select('t.tid', 't.uid', 't.topicCreationTimestamp', 't.title', 't.content')
            ->from(Topic::class, 't')
            ->orderBy('t.topicCreationTimestamp', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery();
    }
}