<?php

namespace App\Controller\API;

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
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use App\Normalizer\UserNormalizer;
use App\Security\TokenAuthenticator;

class UserController extends Controller
{
    /**
     * @Rest\Get("/api/user/list")
     */
    public function index(Request $request)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $users = $this->getDoctrine()->getRepository('App:User')->findAll();
            if (!$users) {
                throw new JsonHttpException(400, 'No users');
            }
            return  $this->json($users);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }

    /**
     * @Rest\Get("/api/user/{id}")
     */
    public function showAction(Request $request, $id)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $oneUser = $this->getDoctrine()->getRepository('App:User')->find($id);
            if (!$oneUser) {
                throw new JsonHttpException(400, 'No user');
            }
            return $this->json($oneUser, 200, [], [AbstractNormalizer::GROUPS => [UserNormalizer::DETAIL]]);
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }
    
    /**
     * @Rest\Delete("/api/user/{id}/delete")
     */
    public function deleteAction(Request $request, $id)
    {
        $apiToken = $request->headers->get(TokenAuthenticator::X_API_KEY);
        $user = $this->getDoctrine()->getRepository(User::class)->findOneBy(['apiToken' => $apiToken]);
        if ($user->getRoles() == ["ROLE_ADMIN", "ROLE_USER"]) {
            $userDel = $this->getDoctrine()->getRepository('App:User')->find($id);
            if (!$content = $request->getContent()) {
                throw new JsonHttpException(400, 'No user');
            }
            $em = $this->getDoctrine()->getManager();
            $em->remove($userDel);
            $em->flush();
            return ($this->json('deleted'));
        } else {
            throw new JsonHttpException(403, 'No permissions for that operation');
        }
    }


    /**
     * @var UserPasswordEncoderInterface
     */
    private $passwordEncoder;


    public function __construct(UserPasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    /**
     * @Rest\Post("/api/registration")
     */
    public function registrationAction(Request $request, SerializerInterface $serializer, ValidatorInterface $validator, UserPasswordEncoderInterface $passwordEncoder)
    {
        /** @var User $user */
        $user = $serializer->deserialize($request->getContent(), User::class, JsonEncoder::FORMAT);
        $errors = $validator->validate($user);
        if (count($errors)) {
            throw new JsonHttpException(400, $errors);
        }
        $password = $passwordEncoder->encodePassword($user, $user->getPassword());
        $user->setPassword($password);
        $user->setApiToken($uuid4 = Uuid::uuid4());
        $this->getDoctrine()->getManager()->persist($user);
        $this->getDoctrine()->getManager()->flush();

        return $this->json($user);
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
