<?php

namespace App\Controller\API;

use App\Entity\ListTodo;
use App\Entity\Item;
use App\Entity\User;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\JsonHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Normalizer\ItemNormalizer;
use App\Security\TokenAuthenticator;

class ItemController extends AbstractController
{

    /**
     * list of all items for admin
     *
     * @Rest\Get("/api/items")
     */
    public function allItemsAction(Request $request)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $users = $this->getDoctrine()->getRepository(Item::class)->findAll();
            if (!$users) {
                throw new JsonHttpException(400, 'No items');
            }
            return  $this->json($users);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }

    /**
     * one item for admin
     *
     * @Rest\Get("/api/item/{id}")
     */
    public function oneItemAction(Request $request, Item $id)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $listTodo = $this->getDoctrine()->getRepository(Item::class)->find($id);
            if (!$listTodo) {
                throw new JsonHttpException(400, 'No item');
            }
            return  $this->json($listTodo, 200, [], [AbstractNormalizer::GROUPS => [ItemNormalizer::DETAIL]]);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }

    /**
     * @Rest\Get("/api/{listTodo}/item/list")
     */
    public function userItemsAction(Request $request, ListTodo $listTodo)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if($listTodo->getUser() != $user){
            throw new JsonHttpException(403, 'No permissions for that items');
        } else {
            $items = $this->getDoctrine()->getRepository(Item::class)->findBy(['listTodo' => $listTodo]);
        }
        if (!$items) {
            throw new JsonHttpException(400, 'No items');
        }
        return $this->json($items);
    }

    /**
     * @Rest\Get("/api/item/{id}")
     */
    public function userItemAction(Request $request, $id)
    {
        $item = $this->getDoctrine()->getRepository('App:Item')->find($id);
        if (!$item) {
            throw new JsonHttpException(400, 'No item');
        }
        return $this->json($item, 200, [], [AbstractNormalizer::GROUPS => [ItemNormalizer::DETAIL]]);
    }

    /**
     * @Rest\Post("/api/item/listTodo/{list}")
     */
    public function createAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ListTodo $list)
    {
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $item = $serializer->deserialize($content, Item::class, 'json');
        $errors = $validator->validate($item);
        if (count($errors)) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $list->addItem($item);
        $em = $this->getDoctrine()->getManager();
        $em->persist($list);
        $em->flush();
        return ($this->json($item));
    }

    /**
     * @Rest\Delete("/api/list/{listTodo}/item/{item}")
     */
    public function deleteAction(ListTodo $listTodo, Item $item)
    {
        $user = $this->getUser();
        $userLists = $user->getListTodo();
        if (isset($listTodo, $userLists)) {
            $items = $listTodo->getItems();
            if (isset($item, $items)) {
                $em = $this->getDoctrine()->getManager();
                $em->remove($item);
                $em->flush();
                return ($this->json('deleted'));
            }
        }
        throw new JsonHttpException(400, 'Bad Request');
    }
}
