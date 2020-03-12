<?php

namespace App\Form;

use App\Entity\Bill;
use App\Entity\BillPlan;
use App\Entity\Customer;
use App\Form\Model\MoneyCustomType;
use App\Repository\BillPlanRepository;
use App\Repository\CustomerRepository;
use App\StockPaymentMethods;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BillType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('description', TextType::class, ['label' => 'bill.fields.description'])
            ->add('note', TextareaType::class, ['label' => 'bill.fields.note'])
            ->add('type', ChoiceType::class, [
                'label' => 'bill.fields.type',
                'choices' => array_flip(Bill::BILL_TYPES),
                'required' => false
            ])
            ->add('dueDate', DateType::class,
                [
                    'label' => 'bill.fields.dueDate',
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => ['class' => 'js-datepicker', 'readonly' => true]
                ]
            )
            ->add('amount', MoneyCustomType::class,
                [
                    'label' => 'bill.fields.amount', 'required' => false,
                    // 'attr' => ['onkeydown' => 'Formata(this,20,event,2);']
                ]
            )
            ->add('paymentDate', DateType::class,
                [
                    'label' => 'bill.fields.paymentDate',
                    'required' => false,
                    'widget' => 'single_text',
                    'html5' => false,
                    'format' => 'dd/MM/yyyy',
                    'attr' => ['class' => 'js-datepicker', 'readonly' => true]
                ]
            )
            ->add('amountPaid', MoneyCustomType::class,
                [
                    'label' => 'bill.fields.amountPaid',
                    // 'attr' => ['onkeydown' => 'Formata(this,20,event,2);']
                ]
            )
            ->add('paymentMethod', ChoiceType::class, [
                'label' => 'bill.fields.paymentMethod',
                'choices' => array_flip(StockPaymentMethods::PAYMENT_METHODS),
            ])
        ;

        $formModifier = function (FormInterface $form, $billType = null) {

            if ($billType === null) {
                $billPlanOptions = [
                    'choices' => []
                ];
            } else {
                $billPlanOptions = [
                    'query_builder' => function (BillPlanRepository $er) use ($billType) {
                        return $er->queryLatestForm($billType);
                    }
                ];
            }

            $form->add('billPlan', EntityType::class,
                array_merge(
                    [
                        'class' => BillPlan::class,
                        'choice_label' => 'getDescriptionWithType',
                        'label' => 'billPlan.title_single',
                        'placeholder' => '',
                    ],
                    $billPlanOptions
                )
            );
        };

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function (FormEvent $event) use ($formModifier) {
                /** @var Bill $data */
                $data = $event->getData();
                $formModifier($event->getForm(), $data->getType());
            }
        );

        $builder->get('type')->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($formModifier) {
                $billType = $event->getForm()->getData();
                $formModifier($event->getForm()->getParent(), $billType);
            }
        );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Bill::class,
        ]);
    }
}
