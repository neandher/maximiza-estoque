<?php

namespace App\Form;

use App\Entity\Address;
use App\Entity\Uf;
use App\Repository\UfRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('street', TextType::class, ['label' => 'address.fields.street'])
            ->add('district', TextType::class, ['label' => 'address.fields.district'])
            ->add('city', TextType::class, ['label' => 'address.fields.city'])
            ->add('zipCode', TextType::class, ['label' => 'address.fields.zipCode'])
            ->add('complement', TextType::class, ['label' => 'address.fields.complement'])
            ->add('uf', EntityType::class, [
                'class' => Uf::class,
                'query_builder' => function (UfRepository $er) {
                    return $er->queryLatest();
                }
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Address::class,
        ]);
    }
}
