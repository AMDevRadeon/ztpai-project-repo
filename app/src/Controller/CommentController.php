<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\ValidJSONStructure;
use App\Service\UniformResponse;
use App\Database\CommentDatabaseQueries;

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
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

use App\Repository\TopicRepository;
use App\Repository\PostRepository;

use Doctrine\ORM\EntityManagerInterface;
use Nelmio\ApiDocBundle\Attribute\Model;
use Nelmio\ApiDocBundle\Attribute\Security;
use OpenApi\Attributes as OA;


final class CommentController extends AbstractController
{
    #[OA\Post(
        description: "Get list of all comments under specified post, paginated" 
    )]
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "Returns list of comments in given post",
        content: new OA\JsonContent(
            type: 'object',
            example: 
                [
                    "desc" => "Response",
                    "status" => 200,
                    "value" => [
                        "pid" => 9,
                        "count" => 1,
                        "posts" =>
                        [
                            [
                                "cid" => 22,
                                "uid" => 12,
                                "commentCreationTimestamp" => "2025-05-30T17:18:49+00:00",
                                "content" => "Inspiring content"
                            ]
                        ]
                    ]
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            Offset and/or limit and/or pid were ommited in request body, 
            Could not find post corresponding to pid
        DESC,
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
                    'pid',
                    'offset',
                    'limit'
                ],
                properties: [
                    new OA\Property(
                        property: 'pid',
                        type: 'integer',
                        description: "Post ID"
                    ),
                    new OA\Property(
                        property: 'offset',
                        type: 'integer',
                        description: "Index from which controller starts listing comments"
                    ),
                    new OA\Property(
                        property: 'limit',
                        type: 'integer',
                        description: "Max count of comments per request. No greater than 128"
                    )
                ]
            ),
            example: [
                "pid" => 8,
                "offset" => 0,
                "limit" => 10
            ]
        )]
    )]
    #[OA\Tag(name: 'Content')]
    #[Route('api/v1/comment/get', name: 'api_get_comment', methods: ['POST'])]
    public function getComments(Request $req,
                                EntityManagerInterface $em,
                                PostRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'pid', 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        $limit = $limit > 128 ? 128 : $limit;

        $post = $repo->find($payload['pid']);

        if (!$post) 
        {
            return $this->json(UniformResponse::createInvalid('Post not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        $result_query_comments = CommentDatabaseQueries::getComments($em, $post->getPid(), $offset, $limit)
            ->getResult();

        $data = [
            'pid' => $post->getPid(),
            'count' => count($result_query_comments),
            'comments' => $result_query_comments,
        ];

        return $this->json(UniformResponse::createValid('Response', $data));
    }


    #[OA\Post(
        description: "Add new comment under a post",
        summary: "Requires JWT from user or higher",
        security: [
            [
                'jwt' => []
            ]
        ]
    )]
    #[OA\RequestBody(
        required: true,
        content: [new OA\MediaType(
            mediaType: 'application/json',
            schema: new OA\Schema(
                required: [
                    'pid',
                    'content'
                ],
                properties: [
                    new OA\Property(
                        property: 'pid',
                        type: 'integer',
                        description: "Post ID"
                    ),
                    new OA\Property(
                        property: 'content',
                        type: 'string',
                        description: "Contents of the comment under post"
                    )
                ]
            ),
            example: [
                "pid" => 18,
                "content" => 'Inspiring content'
            ]
        )]
    )]
    #[OA\Response(
        response: Response::HTTP_CREATED,
        description: "Comment successfully created",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Created new comment on post: Inspiring title",
                    "code": 201
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: <<<DESC
            pid and/or content were ommited in request body,
            post not found,
            post belongs to archived topic,
            post is closed, cannot add new comments
        DESC,
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Missing title key",
                    "status" => 400,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "Trying to access endpoint without credentials",
        content: new OA\JsonContent(
            type: 'object',
            example:
                [
                    "desc" => "Unauthorized",
                    "status" => 401,
                ]
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNPROCESSABLE_ENTITY,
        description: "Constraints not met",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "content: This value is too long. It should have 16384 characters or less.",
                    "code": 422
                }
            EXAMPLE
        )
    )]
    #[OA\Tag(name: 'User')]
    #[Route('api/v1/comment/add', name: 'api_add_comment', methods: ['POST'])]
    public function addComment(Request $req,
                               TokenInterface $sec,
                               ValidatorInterface $validator,
                               PostRepository $repo,
                               EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'pid', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = $repo->find($payload['pid']);

        if (!$post) 
        {
            return $this->json(UniformResponse::createInvalid('Post not found'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getTopic()->getIsArchived())
        {
            return $this->json(UniformResponse::createInvalid('Post belongs to archived topic (read only)'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getIsClosed())
        {
            return $this->json(UniformResponse::createInvalid('Post is closed (no new comments)'),
                               Response::HTTP_BAD_REQUEST);
        }

        $comment = (new Comment())
            ->setPid($post->getPid())
            ->setUid($user->getUid())
            ->setContent($payload['content'])
            ->setUser($user)
            ->setPost($post);

        $errors = $validator->validate($comment);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($comment);
        $em->flush();


        return $this->json(UniformResponse::createValid("Created new comment on post: {$post->getTitle()}", NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }
}