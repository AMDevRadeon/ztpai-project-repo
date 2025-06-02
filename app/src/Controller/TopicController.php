<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Service\ValidJSONStructureService;
use App\Service\UniformResponseService;

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
    #[OA\Post(
        description: "Get a list of all topics, paginated"
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns list of topics",
        content: new OA\JsonContent(
            type: 'object',
            example: 
                [
                    "desc" => "Response",
                    "status" => 200,
                    "value" => [
                        "count" => 1,
                        "topics" =>
                        [
                            [
                                "tid" => 2,
                                "uid" => 10,
                                "topicCreationTimestamp" => "2025-05-30T17:18:49+00:00",
                                "title" => "Inspiring title"
                            ]
                        ]
                    ]
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Offset and/or limit were ommited in request body",
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Missing limit key",
                    "status" => 400,
                ]
        )
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'offset',
                    'limit'
                ],
                properties: [
                    new OA\Property(
                        property: 'offset',
                        type: 'integer',
                        description: "Index from which controller starts listing topics"
                    ),
                    new OA\Property(
                        property: 'limit',
                        type: 'integer',
                        description: "Max count of topics per request. No greater than 128"
                    )
                ]
            ),
            example: [
                "offset" => 0,
                "limit" => 10
            ]
        )]
    )]
    #[OA\Tag(name: 'Content')]
    #[Route('api/v1/topic/get', name: 'api_get_topic', methods: ['POST'])]
    public function getTopics(Request $req,
                              EntityManagerInterface $em,
                              TopicRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        $missing_key = ValidJSONStructureService::checkKeys($payload, 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponseService::createInvalid("Missing $missing_key key"),
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

        return $this->json(UniformResponseService::createValid('Response', $data));
    }
}
