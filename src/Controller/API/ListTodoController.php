<?php

namespace App\Controller\API;

use App\Entity\ListTodo;
use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use App\Exception\JsonHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Normalizer\ListTodoNormalizer;
use App\Security\TokenAuthenticator;

class ListTodoController extends Controller
{

    /**
     * list of all TodoLists for admin
     *
     * @Rest\Get("/api/listsTodo")
     */
    public function allListsAction(Request $request)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $users = $this->getDoctrine()->getRepository(ListTodo::class)->findAll();
            if (!$users) {
                throw new JsonHttpException(400, 'No Lists TODO');
            }
            return  $this->json($users);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }

    /**
     * one TodoList for admin
     *
     * @Rest\Get("/api/listsTodo/{id}")
     */
    public function oneListAction(Request $request, ListTodo $id)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $listTodo = $this->getDoctrine()->getRepository(ListTodo::class)->find($id);
            if (!$listTodo) {
                throw new JsonHttpException(400, 'No List TODO');
            }
            return  $this->json($listTodo, 200, [], [AbstractNormalizer::GROUPS => [ListTodoNormalizer::DETAIL]]);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }

    /**
     * @Rest\Get("api/listTodo/list")
     */
    public function userListAction(Request $request)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        $listsTodo = $this->getDoctrine()->getRepository(ListTodo::class)->findBy(['user' => $user->getId()]);
        if (!$listsTodo) {
            throw new JsonHttpException(400, 'No lists');
        }
        return  $this->json($listsTodo);
    }

    /**
     * @Rest\Get("/api/listTodo/{id}")
     */
    public function oneUserListAction(Request $request, $id)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        $listTodo = $this->getDoctrine()->getRepository(ListTodo::class)
            ->findBy([
                'user' => $user->getId(),
                'id' => $id
            ]);
        if (!$listTodo) {
            throw new JsonHttpException(400, 'No list');
        }
        return $this->json($listTodo, 200, [], [AbstractNormalizer::GROUPS => [ListTodoNormalizer::DETAIL]]);
    }

    /**
     * @Rest\Post("/api/listTodo/create")
     */
    public function createAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $user = $this->getUser();
        $listTodo = $serializer->deserialize($content, ListTodo::class, 'json');
        $errors = $validator->validate($listTodo);
        if (count($errors)) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $user->addListTodo($listTodo);
        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();
        return ($this->json($listTodo));
    }
}
