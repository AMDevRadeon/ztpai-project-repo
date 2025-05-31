<?php

namespace App\Controller;

use App\Entity\Topic;
use App\Entity\Post;
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


final class PostController extends AbstractController
{
    #[Route('api/post/get', name: 'api_get_post', methods: ['POST'])]
    public function getPosts(Request $req,
                              EntityManagerInterface $em,
                              TopicRepository $repo): JsonResponse
    {
        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid', 'offset', 'limit');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $offset = intval($payload["offset"]);
        $limit = intval($payload["limit"]);

        $limit = $limit > 128 ? 128 : $limit;

        $topic = $repo->find($payload['tid']);

        if (!$topic) 
        {
            return $this->json(UniformResponse::createInvalid('Topic not found', Response::HTTP_BAD_REQUEST),
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

        return $this->json(UniformResponse::createValid('Response', $data));
    }

    #[Route('api/post/add', name: 'api_add_post', methods: ['POST'])]
    public function addPost(Request $req,
                            TokenInterface $sec,
                            ValidatorInterface $validator,
                            TopicRepository $repo,
                            EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        {
            return $this->json(UniformResponse::createInvalid('Unauthorized', Response::HTTP_UNAUTHORIZED), 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid', 'title', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(UniformResponse::createInvalid("Missing $missing_key key"),
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) 
        {
            return $this->json(UniformResponse::createInvalid('Topic not found', Response::HTTP_BAD_REQUEST),
                               Response::HTTP_BAD_REQUEST);
        }

        $post = (new Post())
            ->setTid($topic->getTid())
            ->setUid($user->getUid())
            ->setTitle($payload['title'])
            ->setContent($payload['content'])
            ->setIsArchived(false)
            ->setIsClosed(false)
            ->setUser($user)
            ->setTopic($topic);

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponse::createValid("Created new post: {$post->getTitle()}", NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }


    #[Route('api/post/edit', name: 'api_edit_post', methods: ['PATCH'])]
    public function editPost(Request $req,
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

        if ($user->getUid() !== $post->getUid())
        {
            return $this->json(UniformResponse::createInvalid('Cannot edit other users\' posts'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getIsArchived())
        {
            return $this->json(UniformResponse::createInvalid('Post is archived (read only)'),
                               Response::HTTP_BAD_REQUEST);
        }

        if ($post->getTopic()->getIsArchived())
        {
            return $this->json(UniformResponse::createInvalid('Post belongs to archived topic (read only)'),
                               Response::HTTP_BAD_REQUEST);
        }

        $post->setContent("{$post->getContent()}\n[EDIT (" . date("Y-m-d H:i:s") . ")]:\n{$payload['content']}");

        $errors = $validator->validate($post);
        if (count($errors) > 0) {
            return $this->json(UniformResponse::createInvalid(
                                   "{$errors->get(0)->getPropertyPath()}: {$errors->get(0)->getMessage()}", 
                                   Response::HTTP_UNPROCESSABLE_ENTITY),
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($post);
        $em->flush();

        return $this->json(UniformResponse::createValid('Updated', NULL, Response::HTTP_CREATED),
                           Response::HTTP_CREATED);
    }
}
