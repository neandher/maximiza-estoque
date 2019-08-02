<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\Customer;
use App\Entity\Stock;
use App\Form\Model\MoneyCustomType;
use App\Repository\BrandRepository;
use App\Repository\CustomerRepository;
use App\StockPaymentMethods;
use App\StockTypes;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
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
                'choices' => ['stock.types.remove' => StockTypes::TYPE_REMOVE, 'stock.types.add' => StockTypes::TYPE_ADD],
            ])
            ->add('referency', TextType::class, [
                'label' => 'stock.fields.referency'
            ])
            ->add('quantity', NumberType::class, ['label' => 'stock.fields.quantity'])
            ->add('unitPrice', MoneyCustomType::class, ['label' => 'stock.fields.unitPrice'])
            ->add('barCode', TextType::class, [
                'label' => 'stock.fields.barCode',
                'required' => false
            ])
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'query_builder' => function (BrandRepository $er) {
                    return $er->queryLatestForm();
                },
                'choice_label' => 'name',
                'label' => 'brand.title_single',
                'required' => false
            ])
            ->add('customer', EntityType::class, [
                'class' => Customer::class,
                'query_builder' => function (CustomerRepository $er) {
                    return $er->queryLatestForm();
                },
                'choice_label' => 'getNameWithCategory',
                'label' => 'customer.title_single',
                'required' => false
            ])
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'stock.fields.paymentMethod',
                'choices' => [
                    'stock.paymentMethods.cheque' => StockPaymentMethods::CHEQUE,
                    'stock.paymentMethods.cartao' => StockPaymentMethods::CARTAO,
                    'stock.paymentMethods.dinheiro' => StockPaymentMethods::DINHEIRO,
                ],
                'required' => false
            ])
            ->add('obs', TextareaType::class, [
                'label' => 'stock.fields.obs',
                'attr' => [
                    'rows' => 5
                ]
            ])
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
