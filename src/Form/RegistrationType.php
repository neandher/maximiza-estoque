<?php

namespace App\Form;

use App\Form\Model\SwitchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
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
            ->add('receiveEmails', SwitchType::class, ['label' => 'user.fields.receiveEmails'])
            ->add('emailNotifications', EmailType::class, ['label' => 'user.fields.emailNotifications'])
            ->add('roles', ChoiceType::class, [
                'label' => 'user.fields.roles',
                'choices' => [
                    'Nível 1' => 'ROLE_LEVEL_1',
                    'Nível 2' => 'ROLE_LEVEL_2',
                ],
                'multiple' => false,
            ]);

        $builder->get('roles')->addModelTransformer(new CallbackTransformer(
            function ($rolesAsArray) {
                // transform the array to a string
                array_pop($rolesAsArray);
                return implode(', ', $rolesAsArray);
            },
            function ($rolesAsString) {
                // transform the string back to an array
                return explode(', ', $rolesAsString);
            }
        ));

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