<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\User;
use App\Service\ValidJSONStructure;
use App\Service\UniformResponse;

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
    #[Route('api/comment/get', name: 'api_get_comment', methods: ['POST'])]
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

        // TODO: put in database directory or sumfin, i dunno
        $query_builder = $em->createQueryBuilder();
        $result_query_comments = $query_builder
            ->select('t.cid', 't.uid', 't.commentCreationTimestamp', 't.content')
            ->from(Comment::class, 't')
            ->where('t.pid = :current_pid')
            ->orderBy('t.commentCreationTimestamp', 'DESC')
            ->setParameter('current_pid', $post->getPid())
            ->setFirstResult($offset)
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult();

        $data = [
            'pid' => $post->getPid(),
            'count' => count($result_query_comments),
            'comments' => $result_query_comments,
        ];

        return $this->json(UniformResponse::createValid('Response', $data));
    }

    #[Route('api/comment/add', name: 'api_add_comment', methods: ['POST'])]
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