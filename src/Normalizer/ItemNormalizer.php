<?php

namespace App\Normalizer;

use App\Entity\Item;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ItemNormalizer implements NormalizerInterface
{
    const DETAIL = 'Item details';
    const LIST = 'Item list';
    /**
     * @param Item $item
     * @param null $format
     * @param array $context
     * @return array|bool|float|int|string
     */
    public function normalize($item, $format = null, array $context = [])
    {
        $data = [
                'id' => $item->getId(),
                'name' => $item->getName(),
                'checked' => $item->getChecked()
            ];


        if (isset($context[AbstractNormalizer::GROUPS]) && in_array($this::DETAIL, $context[AbstractNormalizer::GROUPS])) {
            $data = [
                    'id' => $item->getId(),
                    'name' => $item->getName(),
                    'checked' => $item->getChecked(),
                    'listTodo' => $item->getListTodo()->getName(),
                    'user' => $item->getListTodo()->getUser()->getApiToken(),
                    'attachment' => $item->getAttachment()->getText()
                ];
        }

        return $data;
    }
    public function supportsNormalization($item, $format = null)
    {
        return $item instanceof Item;
    }
}
