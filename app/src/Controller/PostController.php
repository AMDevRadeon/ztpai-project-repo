<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Entity\User;
use App\Service\ValidJSONStructure;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Query\Parameter;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

use App\Repository\TopicRepository;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;


final class PostController extends AbstractController
{
    #[Route('api/post/get', name: 'api_get_post', methods: ['POST'])]
    public function getTopics(Request $req,
                              EntityManagerInterface $em,
                              TopicRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid', 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(["desc" => "Missing $missing_key", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        $limit = $limit > 128 ? 128 : $limit;

        $topic = $repo->find($payload['tid']);

        if (!$topic) 
        {
            return $this->json(['desc' => 'Topic not found', 'code' => Response::HTTP_BAD_REQUEST],
                                Response::HTTP_BAD_REQUEST);
        }

        // if ($topic->getIsArchived())
        // {
        //     return $this->json(['desc' => 'Topic is archived (read only)', 'code' => Response::HTTP_BAD_REQUEST],
        //                         Response::HTTP_BAD_REQUEST);
        // }

        // TODO: put in database directory or sumfin, i dunno
        $query_builder = $em->createQueryBuilder();
        $result_query_posts = $query_builder
            ->select('t.pid', 't.uid', 't.postCreationTimestamp', 't.title', 't.content', 't.isArchived', 't.isClosed')
            ->from(Post::class, 't')
            ->where('t.tid = :current_tid')
            ->orderBy('t.postCreationTimestamp', 'DESC')
            ->setParameter('current_tid', $topic->getTid())
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        // $query_builder = $em->createQueryBuilder();
        // $result_query_users = $query_builder
        //     ->select('u.uid', 'u.nick', 'u.email', 'u.accCreationTimestamp', 'u.provenance', 'u.motto', 'us.display_email')
        //     ->from(User::class, 'u')
        //     ->where($query_builder->expr()->in('u.uid', array_column($result_query_posts, 'uid')))
        //     ->join('u.settings', 'us')
        //     ->getQuery()
        //     ->getResult();

        // $result_query_users = array_map(function (array $x) {
        //     if (!$x['display_email'])
        //     {
        //         unset($x['email']);
        //     }
        //     return $x;
        // }, $result_query_users);

        $data = [
            'tid' => $topic->getTid(),
            'count' => count($result_query_posts),
            'posts' => $result_query_posts,
            // 'users' => $result_query_users,
        ];

        return $this->json($data);
    }
}
