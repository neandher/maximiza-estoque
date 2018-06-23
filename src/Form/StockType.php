<?php

namespace App\Form;

use App\Entity\Stock;
use App\StockTypes;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class StockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('type', ChoiceType::class, [
                'label' => 'stock.fields.type',
                'choices' => ['stock.types.add' => StockTypes::TYPE_ADD, 'stock.types.remove' => StockTypes::TYPE_REMOVE],
            ])
            ->add('referency', TextType::class, ['label' => 'stock.fields.referency'])
            ->add('quantity', NumberType::class, ['label' => 'stock.fields.quantity'])
            ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Stock::class,
            'stock_type' => null
        ]);
    }
}
