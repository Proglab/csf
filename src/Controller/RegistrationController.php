<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\EmailVerifier;
use App\Security\UserAuthenticator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Guard\GuardAuthenticatorHandler;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;

class RegistrationController extends AbstractController
{
    /**
     * @var EmailVerifier
     */
    private $emailVerifier;

    public function __construct(EmailVerifier $emailVerifier)
    {
        $this->emailVerifier = $emailVerifier;
    }

    /**
     * @Route("/register", name="app_register")
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, GuardAuthenticatorHandler $guardHandler, UserAuthenticator $authenticator, \Swift_Mailer $mailer, VerifyEmailHelperInterface $helper): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // encode the plain password
            $user->setPassword(
                $passwordEncoder->encodePassword(
                    $user,
                    $form->get('plainPassword')->getData()
                )
            );
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($user);
            $entityManager->flush();

            $signatureComponents = $helper->generateSignature(
                'app_verify_email',
                (string) $user->getId(),
                (string) $user->getEmail()
            );

            $message = (new \Swift_Message('Hello Email'))
                ->setFrom('info@proglab.com', 'Proglab')
                ->setTo($user->getEmail(), $user->getFullName())
                ->setSubject('Please Confirm your Email')
                ->setBody(
                    $this->renderView(
                    // templates/emails/registration.html.twig
                        'registration/confirmation_email.html.twig',
                        [
                            'signedUrl' => $signatureComponents->getSignedUrl(),
                            'expiresAt' => $signatureComponents->getExpiresAt(),
                        ]
                    ),
                    'text/html'
                )
            ;

            $mailer->send($message);

            return  $guardHandler->authenticateUserAndHandleSuccess(
                $user,
                $request,
                $authenticator,
                'main' // firewall name in security.yaml
            );
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }

    /**
     * @Route("/verify/email", name="app_verify_email")
     */
    public function verifyUserEmail(Request $request): Response
    {
        $this->denyAccessUnlessGranted('IS_AUTHENTICATED_FULLY');
        /**
         * @var User
         */
        $user = $this->getUser();
        $this->emailVerifier->handleEmailConfirmation($request, $user);
        $this->addFlash('success', 'Your email address has been verified.');

        return $this->redirectToRoute('app_login');
    }
}
