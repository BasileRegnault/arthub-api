<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationSuccessEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserLoginLog;
use App\Enum\AuthEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTLoginSuccessListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    public function __invoke(AuthenticationSuccessEvent $event): void
    {
        $request = $this->requestStack->getCurrentRequest();
        $user = $event->getUser();

        $log = new UserLoginLog();
        $log->setUserConnected($user);
        $log->setEvent(AuthEvent::LOGIN_SUCCESS->value);
        $log->setIp($request?->getClientIp() ?? 'unknown');
        $log->setUserAgent($request?->headers->get('User-Agent'));

        $this->em->persist($log);
        $this->em->flush();
    }
}