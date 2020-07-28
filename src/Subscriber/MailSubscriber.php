<?php

namespace App\Subscriber;

use App\Event\UserRegisteredEvent;
use Swift_Mailer;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use SymfonyCasts\Bundle\VerifyEmail\VerifyEmailHelperInterface;
use Twig\Environment;

class MailSubscriber implements EventSubscriberInterface
{
    /**
     * @var VerifyEmailHelperInterface
     */
    protected $verifyEmailHelper;
    /**
     * @var Swift_Mailer
     */
    protected $mailer;
    /**
     * @var Environment
     */
    protected $templating;

    public function __construct(VerifyEmailHelperInterface $verifyEmailHelper, Swift_Mailer $mailer, Environment $templating)
    {
        $this->verifyEmailHelper = $verifyEmailHelper;
        $this->mailer = $mailer;
        $this->templating = $templating;
    }

    public static function getSubscribedEvents()
    {
        return [
            UserRegisteredEvent::NAME => 'onUserRegistered',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event)
    {
        $user = $event->getUser();
        $signatureComponents = $this->verifyEmailHelper->generateSignature(
            'app_verify_email',
            (string) $user->getId(),
            (string) $user->getEmail()
        );

        $message = (new \Swift_Message())
            ->setFrom('info@proglab.com', 'Proglab')
            ->setTo($user->getEmail(), $user->getFullName())
            ->setSubject('Please Confirm your Email')
            ->setBody(
                $this->templating->render(
                    'registration/confirmation_email.html.twig',
                    [
                        'signedUrl' => $signatureComponents->getSignedUrl(),
                        'expiresAt' => $signatureComponents->getExpiresAt(),
                    ]
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);
    }
}
