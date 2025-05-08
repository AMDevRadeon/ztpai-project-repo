<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;

// Discussions
class DiscussionController extends AbstractController 
{
    #[Route('api/discussions/{discussion}/{comment}', methods: ['GET'], requirements: ['discussion' => '\d+', 'comment' => '\d+'])]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returned comments under different discussions",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                "commenterID": "1",
                "comment": "A bo tak",
                "commentScore": 5
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_NOT_FOUND,
        description: "No comments with such ID under specified discussion",
        content: new OA\JsonContent(
            type: 'object',
        )
    )]
    #[OA\Tag(name: 'Discussions')]
    public function show(string $discussion, string $comment = '0'): Response
    {
        $discussions = [
            '1' => [
                '1' => [
                    'commenterID' => '2',
                    'comment' => "A na co to komu?",
                    'commentScore' => 21
                ],
                '2' => [
                    'commenterID' => '1',
                    'comment' => "A bo tak",
                    'commentScore' => 5
                ]
            ],
            '2' => [
                '1' => [
                    'commenterID' => '1',
                    'comment' => "Pierwszy komentarz",
                    'commentScore' => 7
                ],
                '2' => [
                    'commenterID' => '2',
                    'comment' => "A ten jest drugi",
                    'commentScore' => 2
                ]
            ]
        ];

        $response = new Response();

        if (array_key_exists($discussion, $discussions)) {
            if (array_key_exists($comment, $discussions[$discussion])) {
                $response->setContent(json_encode($discussions[$discussion][$comment]));
            }
            else if ($comment === '0') {
                $response->setContent(json_encode($discussions[$discussion]));
            }

            $response->setStatusCode(Response::HTTP_OK);
    
            $response->headers->set('Content-Type', 'application/json');
        }
        else {
            $response->setStatusCode(Response::HTTP_NOT_FOUND);
        }
        
        return $response;
    }
}