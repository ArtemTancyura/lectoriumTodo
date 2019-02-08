<?php

namespace App\Normalizer;

use App\Entity\User;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class UserNormalizer implements NormalizerInterface
{
    const DETAIL = 'user details';
    /**
     * @param User $user
     * @param null $format
     * @param array $context
     * @return array|bool|float|int|string
     */
    public function normalize($user, $format = null, array $context = [])
    {
        $data = [
            'id' => $user->getId(),
            'apiToken' => $user->getApiToken()
        ];


        if (isset($context[AbstractNormalizer::GROUPS]) && in_array($this::DETAIL, $context[AbstractNormalizer::GROUPS])) {
            $data = [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'apiToken' => $user->getApiToken(),
                'roles' => $user->getRoles()
            ];
        }

        return $data;
    }
    public function supportsNormalization($user, $format = null)
    {
        return $user instanceof User;
    }
}
