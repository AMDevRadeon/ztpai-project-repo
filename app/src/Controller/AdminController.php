<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserRole;
use App\Entity\UserSettings;
use App\Entity\Topic;
use App\Repository\UserRepository;
use App\Repository\TopicRepository;
use App\Service\ValidJSONStructure;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

use OpenApi\Attributes as OA;


final class AdminController extends AbstractController
{
    #[OA\Response(
        response: Response::HTTP_OK,
        description: "User deleted",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Deleted",
                    "code": "200"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_BAD_REQUEST,
        description: "Invalid request made in this context",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "User not found",
                    "code": "401"
                }
            EXAMPLE
        )
    )]
    #[OA\Response(
        response: Response::HTTP_UNAUTHORIZED,
        description: "User not authorized",
        content: new OA\JsonContent(
            type: 'object',
            example: <<<EXAMPLE
                {
                    "desc": "Unauthorized",
                    "code": "401"
                }
            EXAMPLE
        )
    )]    
    #[OA\RequestBody(
        required: true,
        content: new OA\JsonContent(
            type: Object::class,
            example: [
                "uid" => "10"
            ]
        )
    )]
    #[OA\Tag(name: 'Admin')]
    #[Route('api/admin/user/delete', name: 'api_admin_user_delete', methods: ['DELETE'])]
    public function deleteUser(Request $req,
                               TokenInterface $sec,
                               UserRepository $repo,
                               EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED], 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'uid');

        // Required fields
        if ($missing_key !== NULL) 
        {
            return $this->json(["desc" => "Missing $missing_key", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $u = $repo->find($payload["uid"]);
        if (!$u) 
        {
            return $this->json(['desc' => 'User not found', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        if ($u->getPasshash() === '-')
        {
            return $this->json(['desc' => 'Already deleted', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        if ($user->getUid() === $u->getUid())
        {
            return $this->json(['desc' => 'Trying to delete self', 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $u->setNick('Deleted_User_' . strval(time()));
        $u->setPasshash('-');
        $u->setProvenance('');
        $u->setMotto('');
        $u->getSettings()->setDisplayEmail(false);

        $em->persist($u);
        $em->flush();

        return $this->json(['desc' => 'Deleted', 'code' => Response::HTTP_OK]);
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/admin/topic/add', name: 'api_admin_topic_add', methods: ['POST'])]
    public function addTopic(Request $req,
                             TokenInterface $sec,
                             ValidatorInterface $validator,
                             EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED], 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'title', 'content');

        if ($missing_key !== NULL)
        {
            return $this->json(["desc" => "Missing $missing_key", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = (new Topic())
            ->setUid($user->getUid())
            ->setTitle(strval($payload['title']))
            ->setContent(strval($payload['content']))
            ->setUser($user);

        $errors = $validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(['desc' => $errors->get(0)->getPropertyPath() . ': ' . $errors->get(0)->getMessage(), 'code' => Response::HTTP_UNPROCESSABLE_ENTITY],
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(['desc' => "Created new topic: " . $topic->getTitle(), 'code' => Response::HTTP_CREATED],
                           Response::HTTP_CREATED);
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/admin/topic/delete', name: 'api_admin_topic_delete', methods: ['DELETE'])]
    public function deleteTopic(Request $req,
                                TokenInterface $sec,
                                TopicRepository $repo,
                                ValidatorInterface $validator,
                                EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED], 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(["desc" => "Missing $missing_key", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(['desc' => 'Topic not found', 'code' => Response::HTTP_BAD_REQUEST],
                                Response::HTTP_BAD_REQUEST);
        }

        $em->remove($topic);
        $em->flush();

        return $this->json(['desc' => 'Deleted', 'code' => Response::HTTP_OK]);
    }


    #[OA\Tag(name: 'Admin')]
    #[Route('api/admin/topic/edit', name: 'api_admin_topic_edit', methods: ['PATCH'])]
    public function editTopic(Request $req,
                              TokenInterface $sec,
                              TopicRepository $repo,
                              ValidatorInterface $validator,
                              EntityManagerInterface $em): JsonResponse
    {
        $user = $sec->getUser();
        if (!$user) 
        { 
            return $this->json(['desc' => 'Unauthorized', 'code' => Response::HTTP_UNAUTHORIZED], 
                               Response::HTTP_UNAUTHORIZED);
        }

        $payload = $req->toArray();

        $missing_key = ValidJSONStructure::checkKeys($payload, 'tid');

        if ($missing_key !== NULL)
        {
            return $this->json(["desc" => "Missing $missing_key", 'code' => Response::HTTP_BAD_REQUEST],
                               Response::HTTP_BAD_REQUEST);
        }

        $topic = $repo->find($payload['tid']);

        if (!$topic) {
            return $this->json(['desc' => 'Topic not found', 'code' => Response::HTTP_BAD_REQUEST],
                                Response::HTTP_BAD_REQUEST);
        }

        if (isset($payload['title'])) 
        {
            $topic->setTitle($payload['title'] ?: null);
        }

        if (isset($payload['content'])) 
        {
            $topic->setContent($payload['content'] ?: null);
        }

        if (isset($payload['archived'])) 
        {
            $topic->setIsArchived($payload['archived'] ?: false);
        }

        $errors = $validator->validate($topic);
        if (count($errors) > 0) {
            return $this->json(['desc' => $errors->get(0)->getPropertyPath() . ': ' . $errors->get(0)->getMessage(), 'code' => Response::HTTP_UNPROCESSABLE_ENTITY],
                               Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $em->persist($topic);
        $em->flush();

        return $this->json(['desc' => 'Updated', 'code' => Response::HTTP_OK]);
    }
}
