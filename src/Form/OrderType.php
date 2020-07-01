<?php

namespace App\Form;

use App\Entity\Order;
use App\Form\Model\MoneyCustomType;
use App\StockPaymentMethods;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class OrderType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('subtotal', HiddenType::class)
            ->add('subtotalView', TextType::class, ['label' => 'order.fields.subtotal', 'mapped' => false, 'disabled' => true])
            ->add('discount', TextType::class, ['label' => 'order.fields.discount'])
            ->add('total', HiddenType::class)
            ->add('totalView', TextType::class, ['label' => 'order.fields.total', 'mapped' => false, 'disabled' => true])
            ->add('client', TextType::class, [
                'label' => 'order.fields.client'
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'stock.fields.paymentMethod',
                'choices' => [
                    'stock.paymentMethods.cheque' => StockPaymentMethods::CHEQUE,
                    'stock.paymentMethods.cartao' => StockPaymentMethods::CARTAO,
                    'stock.paymentMethods.dinheiro' => StockPaymentMethods::DINHEIRO,
                ],
                'required' => false,
                'multiple' => true,
                'attr' => ['class' => 'm-select2']
            ])
            ->add('orderItems', CollectionType::class, [
                'entry_type' => OrderItemsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'order.fields.items',
            ]);

        $builder->get('discount')->addModelTransformer(new CallbackTransformer(
            function ($value) {
                return $value ? str_replace('.', ',', $value) : $value;
            },
            function ($value) {
                return $value ? str_replace(',', '.', $value) : $value;
            }
        ));

        $builder->get('paymentMethod')->addModelTransformer(new CallbackTransformer(
            function ($array) {
                return $array ? explode(',', $array) : $array;
            },
            function ($string) {
                return count($string) > 0 ? implode(',', $string) : null;
            }
        ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
