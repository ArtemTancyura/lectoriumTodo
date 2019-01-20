<?php

namespace App\Controller;

use App\Entity\User;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use App\Exception\JsonHttpException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Ramsey\Uuid\Uuid;
use FOS\RestBundle\Controller\Annotations as Rest;
class UserController extends Controller
{
    /**
     * @Rest\Get("/api/user/list")
     */
    public function index(Request $request)
    {
        $users = $this->getDoctrine()->getRepository('App:User')->findAll();
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'No users');
        }
        return  $this->json($users);
    }

    /**
     * @Rest\Get("/api/user/{id}")
     */
    public function showAction(Request $request, $id)
    {
        $user = $this->getDoctrine()->getRepository('App:User')->find($id);
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(400, 'No user');
        }
        return $this->json($user);
    }

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;
    public function __construct(SerializerInterface $serializer, UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->serializer = $serializer;
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Rest\Post("/api/registration")
     */
    public function registrationAction(Request $request, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var User $user */
        $user = $this->serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT);
        $errors = $validator->validate($user);
        if (count($errors)) {
            throw new JsonHttpException(400, 'Bad Request');
        }
        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $user->setApiToken($uuid4 = Uuid::uuid4());
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json($user->getApiToken());
    }

    /**
     * @Rest\Post("/api/login", methods={"POST"})
     */
    public function loginAction(Request $request)
    {
        if (!$content = $request->getContent()) {
            throw new JsonHttpException(Response::HTTP_BAD_REQUEST, 'Bad Request');
        }
        $data = json_decode($content, true);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['email'=>$data['email']]);
        if ($user instanceof User) {
            if ($this->passwordEncoder->isPasswordValid($user, $data['password'])) {
                return ($this->json(['user'=>$user]));
            }
        }
        throw new JsonHttpException(Response::HTTP_BAD_REQUEST, 'Bad Request1');
    }
}