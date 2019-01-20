<?php

namespace App\Controller;

use App\Entity\Attachment;
use App\Entity\ListTodo;
use App\Entity\Item;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use App\Exception\JsonHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

class AttachmentController extends AbstractController
{
    /**
     * @Rest\Get("api/items/attachment/list")
     */
    public function index(Request $request)
    {
        $items = $this->getDoctrine()->getRepository('App:Attachment')->findAll();
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'No attachments');
        }
        return  $this->json($items);
    }

    /**
     * @Rest\Get("/api/item/{id}/attachment")
     */
    public function showAction(Request $request, $id)
    {
        $item = $this->getDoctrine()->getRepository('App:Attachment')->find($id);
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'No attachment');
        }
        return $this->json($item);
    }

    /**
     * @Rest\Post("/api/list/{list}/item/{item}/attachment")
     */
    public function createAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, ListTodo $list, Item $item)
    {
        $user = $this->getUser();
        $userLists = $user->getListTodo()->getName();
        if (isset($list, $userLists)) {
            $items = $list->getItem();
            if (isset($item, $items)) {
                $attachment = $serializer->deserialize($request->getContent(), Attachment::class, 'json');
                $errors = $validator->validate($attachment);
                if (count($errors)) {
                    throw new JsonHttpException(400, 'Bad Request');
                }
                $repository = $this->getDoctrine()->getRepository(Item::class);
                $item = $repository->findOneBy(['name'=> $item], []);

                $item->setAttachment($attachment);
                $em = $this->getDoctrine()->getManager();
                $em->persist($item);
                $em->flush();
                return ($this->json($item));
            }
        }
        throw new JsonHttpException(400, 'Bad Request');
    }
}