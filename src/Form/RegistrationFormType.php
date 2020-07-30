<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    public const EMAIL_MIN_CHAR = 6;
    public const EMAIL_MAX_CHAR = 254;
    public const PASSWORD_MIN_CHAR = 6;
    public const PASSWORD_MAX_CHAR = 254;

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
                        'min' => self::EMAIL_MIN_CHAR,
                        'minMessage' => 'Your email should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => self::EMAIL_MAX_CHAR,
                    ]),
                ],
            ])
            ->add('agreeTerms', CheckboxType::class, [
                'mapped' => false,
                'constraints' => [
                    new IsTrue([
                        'message' => 'You should agree to our terms.',
                    ]),
                ],
            ])
            ->add('plainPassword', PasswordType::class, [
                // instead of being set onto the object directly,
                // this is read and encoded in the controller
                'mapped' => false,
                'constraints' => [
                    new NotBlank([
                        'message' => 'Please enter a password',
                    ]),
                    new Length([
                        'min' => self::PASSWORD_MIN_CHAR,
                        'minMessage' => 'Your password should be at least {{ limit }} characters',
                        // max length allowed by Symfony for security reasons
                        'max' => self::PASSWORD_MAX_CHAR,
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
