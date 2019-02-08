<?php

namespace App\Controller\API;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;

class PaymentController extends AbstractController
{
    /**
     * @Rest\Post("/api/payment")
     */
    public function index(Request $request)
    {
        $token = $request->request->get('id');

        \Stripe\Stripe::setApiKey("sk_test_0b8JwFXSEJuiVMhdu6jexUlc");

        \Stripe\Charge::create(array(
            "amount" => 2000,
            "currency" => "usd",
            "source" => $token,
            "description" => "First test charge!"
        ));

        return $this->json('yeah');
    }

}