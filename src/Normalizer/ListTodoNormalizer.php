<?php

namespace App\Normalizer;

use App\Entity\ListTodo;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ListTodoNormalizer implements NormalizerInterface
{
    const DETAIL = 'listTodo details';
    /**
     * @param ListTodo $listTodo
     * @param null $format
     * @param array $context
     * @return array|bool|float|int|string
     */
    public function normalize($listTodo, $format = null, array $context = [])
    {
        $data = [
            'id' => $listTodo->getId(),
            'name' => $listTodo->getName()
        ];


        if (isset($context[AbstractNormalizer::GROUPS]) && in_array($this::DETAIL, $context[AbstractNormalizer::GROUPS])) {
            $data = [
                'id' => $listTodo->getId(),
                'name' => $listTodo->getName(),
                'user'=> $listTodo->getUser()->getApiToken()
            ];
        }

        return $data;
    }
    public function supportsNormalization($listTodo, $format = null)
    {
        return $listTodo instanceof ListTodo;
    }
}
