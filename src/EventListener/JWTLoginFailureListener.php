<?php

namespace App\EventListener;

use Lexik\Bundle\JWTAuthenticationBundle\Event\AuthenticationFailureEvent;
use Doctrine\ORM\EntityManagerInterface;
use App\Entity\UserLoginLog;
use App\Enum\AuthEvent;
use Symfony\Component\HttpFoundation\RequestStack;

class JWTLoginFailureListener
{
    public function __construct(
        private EntityManagerInterface $em,
        private RequestStack $requestStack
    ) {}

    public function __invoke(AuthenticationFailureEvent $event)
    {
        $request = $this->requestStack->getCurrentRequest();

        $log = new UserLoginLog();
        $log->setEvent(AuthEvent::LOGIN_FAILED->value);
        $log->setIp($request?->getClientIp() ?? 'unknown');
        $log->setUserAgent($request?->headers->get('User-Agent'));

        $this->em->persist($log);
        $this->em->flush();
    }
}
