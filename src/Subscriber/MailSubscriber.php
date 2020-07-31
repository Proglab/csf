<?php

namespace App\Subscriber;

use App\Event\LostPasswordEvent;
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
            LostPasswordEvent::NAME => 'onUserLostPassword',
        ];
    }

    public function onUserRegistered(UserRegisteredEvent $event): void
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

    public function onUserLostPassword(LostPasswordEvent $event): void
    {
        $user = $event->getUser();

        $message = (new \Swift_Message())
            ->setFrom('info@proglab.com', 'Proglab')
            ->setTo($user->getEmail(), $user->getFullName())
            ->setSubject('Your password reset request')
            ->setBody(
                $this->templating->render(
                    'reset_password/email.html.twig',
                    [
                        'resetToken' => $event->getResetToken(),
                        'tokenLifetime' => $event->getTokenLifetime(),
                    ]
                ),
                'text/html'
            )
        ;
        $this->mailer->send($message);
    }
}
