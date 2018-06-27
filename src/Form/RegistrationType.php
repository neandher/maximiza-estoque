<?php

namespace App\Form;

use App\Form\Model\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'user.fields.firstName'])
            ->add('lastName', TextType::class, ['label' => 'user.fields.lastName'])
            ->add('receiveEmails', SwitchType::class, ['label' => 'user.fields.receiveEmails']);

        if ($options['is_edit'] == true) {
            $builder->remove('plainPassword');
        }
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\RegistrationFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_registration';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'is_edit' => false,
            'validation_groups' => ['Registration']
        ]);
    }


}