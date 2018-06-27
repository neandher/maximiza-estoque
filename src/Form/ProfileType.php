<?php

namespace App\Form;

use App\Form\Model\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ProfileType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, ['label' => 'user.fields.firstName'])
            ->add('lastName', TextType::class, ['label' => 'user.fields.lastName'])
            ->add('receiveEmails', SwitchType::class, ['label' => 'user.fields.receiveEmails'])
        ;
    }

    public function getParent()
    {
        return 'FOS\UserBundle\Form\Type\ProfileFormType';
    }

    public function getBlockPrefix()
    {
        return 'app_user_profile';
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'validation_groups' => ['Profile']
        ]);
    }
}