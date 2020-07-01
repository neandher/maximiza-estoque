<?php

namespace App\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;

class ReferencyType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getBlockPrefix()
    {
        return 'referency';
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return TextType::class;
    }

}