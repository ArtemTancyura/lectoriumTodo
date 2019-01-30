<?php

namespace App\Controller;

use App\Entity\ListTodo;
use App\Entity\Item;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\JsonHttpException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use FOS\RestBundle\Controller\Annotations as Rest;
class ItemController extends AbstractController
{
    /**
     * @Rest\Get("api/item/list")
     */
    public function index(Request $request)
    {
        $items = $this->getDoctrine()->getRepository('App:Item')->findAll();
        if (!$items) {
            throw new JsonHttpException(400, 'No items');
        }
        return  $this->json($items);
    }

    /**
     * @Rest\Get("/api/item/{id}")
     */
    public function showAction(Request $request, $id)
    {
        $item = $this->getDoctrine()->getRepository('App:Item')->find($id);
        if (!$item) {
            throw new JsonHttpException(400, 'No item');
        }
        return $this->json($item);
    }

    /**
     * @Rest\Post("/api/item/listTodo/{id}")
     */
    public function createAction(Request $request, SerializerInterface $serializer,ValidatorInterface $validator, $id)
    {
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $item = $serializer->deserialize($content,Item::class,'json');
        $errors = $validator->validate($item);
        if (count($errors)) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $repository = $this->getDoctrine()->getRepository(ListTodo::class);
        $list = $repository->findOneBy(['id'=> $id]);

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
        if(isset($listTodo, $userLists)){
            $items = $listTodo->getItems();
            if(isset($item, $items)){
                $em = $this->getDoctrine()->getManager();
                $em->remove($item);
                $em->flush();
                return ($this->json('deleted'));
            }
        }
        throw new JsonHttpException(400, 'Bad Request');
    }


}