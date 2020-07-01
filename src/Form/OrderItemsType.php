<?php

namespace App\Form;

use App\Entity\OrderItems;
use App\Form\Model\MoneyCustomType;
use App\Form\Model\ReferencyType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderItemsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('referency', ReferencyType::class, ['label' => 'orderItems.fields.referency'])
            ->add('quantity', NumberType::class, ['label' => 'orderItems.fields.quantity'])
            ->add('subtotal', HiddenType::class, ['label' => 'orderItems.fields.subtotal'])
            // ->add('discount', MoneyCustomType::class, ['label' => 'orderItems.fields.discount'])
            ->add('total', HiddenType::class)
            ->add('totalView', TextType::class, ['label' => 'orderItems.fields.total', 'mapped' => false, 'attr' => ['disabled' => true]])
            ->add('price', TextType::class, ['label' => 'orderItems.fields.price']);

        $builder->get('price')->addModelTransformer(new CallbackTransformer(
            function ($value) {
                return $value ? str_replace('.', ',', $value) : $value;
            },
            function ($value) {
                return $value ? str_replace(',', '.', $value) : $value;
            }
        ));

        $builder->addEventListener(
            FormEvents::POST_SET_DATA,
            function (FormEvent $event) {

                /** @var OrderItems $orderItem */
                $orderItem = $event->getData();

                if ($orderItem && $orderItem->getId()) {
                    $event->getForm()
                        ->remove('referency')
                        ->add('referency', ReferencyType::class, [
                            'label' => 'orderItems.fields.referency',
                            'attr' => ['readonly' => true]
                        ])
                        ->remove('price')
                        ->add('price', TextType::class, [
                            'label' => 'orderItems.fields.price',
                            'attr' => ['readonly' => true]
                        ]);
                }
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderItems::class,
        ]);
    }
}
