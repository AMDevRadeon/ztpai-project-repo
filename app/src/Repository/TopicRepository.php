<?php

namespace App\Repository;

use App\Entity\Topic;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class TopicRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Topic::class);
    }

    // public function getTopicsPaginated(int $offset, int $limit): string
    // {
        // return $this->createQueryBuilder('')
        //     ->select('t.tid', 't.uid', 't.topicCreationTimestamp', 't.title', 't.content')
        //     ->from(Topic::class, 'u')
        //     ->orderBy('t.topicCreationTimestamp', 'DESC')
        //     ->setFirstResult($offset)
        //     ->setMaxResults($limit)
        //     ->getQuery()
            // ->getResult();
            // ->getSQL();

        // return $this->createQuery("SELECT t.tid, t.uid, t.topicCreationTimestamp, t.title, t.content FROM App\Entity\Topic t ORDER BY t.topicCreationTimestamp DESC LIMIT $limit")
        //     ->getResult();
    // }

//    /**
//     * @return User[] Returns an array of User objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('u.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?User
//    {
//        return $this->createQueryBuilder('u')
//            ->andWhere('u.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
