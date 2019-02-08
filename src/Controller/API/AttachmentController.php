<?php

namespace App\Controller\API;

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
        $attachments = $this->getDoctrine()->getRepository('App:Attachment')->findAll();
        if (!$attachments) {
            throw new JsonHttpException(400, 'No attachments');
        }
        return  $this->json($items);
    }

    /**
     * @Rest\Get("/api/item/{id}/attachment")
     */
    public function showAction(Request $request, $id)
    {
        $attachment = $this->getDoctrine()->getRepository('App:Attachment')->find($id);
        if (!$attachment) {
            throw new JsonHttpException(400, 'No attachment');
        }
        return $this->json($item);
    }

    /**
     * @Rest\Post("/api/list/{id2}/item/{id}/attachment")
     */
    public function createAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, $id2, $id)
    {
        $user = $this->getUser();
        $userLists = $user->getListTodo();

        $repository = $this->getDoctrine()->getRepository(ListTodo::class);
        $listTodo = $repository->findOneBy(['id'=> $id2]);

        $repository = $this->getDoctrine()->getRepository(Item::class);
        $item = $repository->findOneBy(['id'=> $id]);

        if (isset($listTodo, $userLists)) {
            $items = $listTodo->getItems();
            if (isset($item, $items)) {
                $attachment = $serializer->deserialize($request->getContent(), Attachment::class, 'json');
                $errors = $validator->validate($attachment);
                if (count($errors)) {
                    throw new JsonHttpException(400, 'Bad Request');
                }
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
