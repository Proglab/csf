<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProfileFormType extends AbstractType
{
    /**
     * @param FormBuilderInterface<object> $builder
     * @param array<string|int, string>    $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('firstname', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a first name',
                    ]),
                ],
            ])
            ->add('lastname', null, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a last name',
                    ]),
                ],
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a email',
                    ]),
                    new Length([
                        'min' => 6,
                        'minMessage' => 'Your email should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => 254,
                    ]),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
