<?php

namespace App\Form;

use App\Entity\Customer;
use App\Entity\CustomerCategory;
use App\Entity\CustomerState;
use App\Repository\CustomerCategoryRepository;
use App\Repository\CustomerStateRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'resource.fields.name'
            ])
            ->add('email', EmailType::class, [
                'label' => 'customer.fields.email'
            ])
            ->add('cnpj', TextType::class, [
                'label' => 'customer.fields.cnpj',
                'attr' => ['class' => "mask_cnpj"]
            ])
            ->add('phoneNumber', TextType::class, [
                'label' => 'customer.fields.phoneNumber'
            ])
            ->add('state', EntityType::class, [
                'class' => CustomerState::class,
                'query_builder' => function (CustomerStateRepository $er) {
                    return $er->queryLatestForm();
                },
                'choice_label' => 'name',
                'label' => 'customerState.title_single',
            ])
            ->add('category', EntityType::class, [
                'class' => CustomerCategory::class,
                'query_builder' => function (CustomerCategoryRepository $er) {
                    return $er->queryLatestForm();
                },
                'choice_label' => 'name',
                'label' => 'customerCategory.title_single',
            ])
            ->add('customerAddresses', CollectionType::class, [
                'entry_type' => CustomerAddressesType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'address.title_single',
            ])
            ->add('customerObservations', CollectionType::class, [
                'entry_type' => CustomerObservationsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'customerObservations.title',
            ])
            ->add('customerBrands', CollectionType::class, [
                'entry_type' => CustomerBrandsType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'label' => 'brand.title_single',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Customer::class,
        ]);
    }
}
