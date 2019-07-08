<?php

namespace App\Form;

use App\Entity\Brand;
use App\Entity\CustomerBrands;
use App\Repository\BrandRepository;
use App\Repository\CustomerBrandsRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CustomerBrandsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('brand', EntityType::class, [
                'class' => Brand::class,
                'query_builder' => function (BrandRepository $er) {
                    return $er->queryLatestForm();
                },
                'choice_label' => 'name',
                'label' => 'brand.title_single',
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CustomerBrands::class,
        ]);
    }
}
