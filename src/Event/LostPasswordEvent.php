<?php

namespace App\Event;

use App\Entity\User;
use Symfony\Contracts\EventDispatcher\Event;
use SymfonyCasts\Bundle\ResetPassword\Model\ResetPasswordToken;

class LostPasswordEvent extends Event
{
    public const NAME = 'user.lost_password';

    /**
     * @var User
     */
    protected $user;
    /**
     * @var ResetPasswordToken
     */
    protected $resetToken;
    /**
     * @var int
     */
    protected $tokenLifetime;

    public function __construct(User $user, ResetPasswordToken $resetToken, int $tokenLifetime)
    {
        $this->user = $user;
        $this->resetToken = $resetToken;
        $this->tokenLifetime = $tokenLifetime;
    }

    public function getUser(): User
    {
        return $this->user;
    }

    public function getResetToken(): ResetPasswordToken
    {
        return $this->resetToken;
    }

    public function getTokenLifetime(): int
    {
        return $this->tokenLifetime;
    }
}
