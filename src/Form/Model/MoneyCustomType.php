<?php

namespace App\Form\Model;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MoneyCustomType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'currency' => 'BRL',
            'grouping' => true,
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getParent()
    {
        return MoneyType::class;
    }
}
