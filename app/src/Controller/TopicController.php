<?php

namespace App\Controller;

use App\Entity\Topic;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use App\Repository\TopicRepository;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;


final class TopicController extends AbstractController
{
    #[Route('api/topic/get', name: 'api_get_topic', methods: ['POST'])]
    public function getTopics(Request $req,
                              EntityManagerInterface $em,
                              TopicRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        // TODO: make this a service that checks required JSON parameters
        // Required fields
        if (!isset($payload['offset']))
        {
            return $this->json(["desc" => "Missing offset", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        if (!isset($payload['limit'])) 
        {
            return $this->json(["desc" => "Missing limit", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        // Database logic
        $query_builder = $em->createQueryBuilder();
        $result_query = $query_builder
            ->select('t.tid', 't.uid', 't.topicCreationTimestamp', 't.title', 't.content')
            ->from(Topic::class, 't')
            ->orderBy('t.topicCreationTimestamp', 'DESC')
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = [
            'count' => count($result_query),
            'topics' => $result_query
        ];

        return $this->json($data);
    }
}
