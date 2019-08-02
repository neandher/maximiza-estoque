<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\BillPlanCategory;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillPlanCategoryType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, ['label' => 'resource.fields.name'])
            ->add('billType', ChoiceType::class, [
                'label' => 'billPlanCategory.fields.type',
                'choices' => array_flip(Bill::BILL_TYPES)
            ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillPlanCategory::class,
        ]);
    }
}
