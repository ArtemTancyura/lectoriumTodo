<?php

namespace App\Controller;

use App\Entity\ListTodo;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use App\Exception\JsonHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use FOS\RestBundle\Controller\Annotations as Rest;

class ListTodoController extends Controller
{
    /**
     * @Rest\Get("api/listTodo/list")
     */
    public function index(Request $request)
    {
        $listsTodo = $this->getDoctrine()->getRepository('App:ListTodo')->findAll();
        if (!$listsTodo) {
            throw new JsonHttpException(400, 'No lists');
        }
        return  $this->json($listsTodo);
    }

    /**
     * @Rest\Get("/api/listTodo/{id}")
     */
    public function showAction(Request $request, $id)
    {
        $listTodo = $this->getDoctrine()->getRepository('App:ListTodo')->find($id);
        if (!$listTodo) {
            throw new JsonHttpException(400, 'No list');
        }
        return $this->json($listTodo);
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
        $listTodo = $serializer->deserialize($content,ListTodo::class,'json');
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