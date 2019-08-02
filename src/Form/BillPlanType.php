<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\BillPlan;
use App\Entity\BillPlanCategory;
use App\Repository\BillPlanCategoryRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillPlanType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, ['label' => 'resource.fields.name'])
            ->add('billPlanCategory', EntityType::class,
                [
                    'class' => BillPlanCategory::class,
                    'query_builder' => function (BillPlanCategoryRepository $er) {
                        return $er->queryLatestForm();
                    },
                    'choice_label' => 'description',
                    'label' => 'billPlanCategory.title_single'
                ]);
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BillPlan::class,
        ]);
    }
}
