<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Service\ValidJSONStructure;
use App\Service\UniformResponse;

use App\Database\TopicDatabaseQueries;

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

        $missing_key = ValidJSONStructure::checkKeys($payload, 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        $limit = $limit > 128 ? 128 : $limit;

        $result_query = TopicDatabaseQueries::getTopics($em, $offset, $limit)
            ->getResult();

        $data = [
            'count' => count($result_query),
            'topics' => $result_query
        ];

        return $this->json(UniformResponse::createValid('Response', $data));
    }
}
