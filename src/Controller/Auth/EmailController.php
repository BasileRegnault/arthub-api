<?php 

namespace App\Controller\Auth;

use App\Entity\User;
use App\Entity\PasswordResetToken;
use App\Enum\AuthEvent;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;

class EmailController extends AbstractController
{
    #[Route('/api/forgot-password', methods: ['POST'])]
    public function forgotPassword(
        Request $request,
        EntityManagerInterface $em,
        MailerInterface $mailer
    ) {
        $email = $request->toArray()['email'] ?? null;

        if (!$email) {
            return $this->json(['message' => 'Email requis'], 400);
        }

        $token = bin2hex(random_bytes(32));

        $reset = new PasswordResetToken();
        $reset->setEmail($email);
        $reset->setToken($token);
        $reset->setExpiresAt(new \DateTimeImmutable('+1 hour'));

        $em->persist($reset);
        $em->flush();

        $link = "http://localhost:4200/reset-password?token=$token";

        $message = (new Email())
            ->to($email)
            ->subject('Réinitialisation du mot de passe')
            ->html("<p>Cliquez ici :</p><a href='$link'>$link</a>");

        $mailer->send($message);

        return $this->json(['message' => 'Email envoyé']);
    }

    #[Route('/api/reset-password', methods: ['POST'])]
    public function resetPassword(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $hasher
    ) {
        $data = $request->toArray();
        $token = $data['token'] ?? null;
        $password = $data['password'] ?? null;

        $reset = $em->getRepository(PasswordResetToken::class)
            ->findOneBy(['token' => $token]);

        if (!$reset || $reset->getExpiresAt() < new \DateTime()) {
            return $this->json(['message' => 'Token invalide ou expiré'], 400);
        }

        $user = $em->getRepository(User::class)
            ->findOneBy(['email' => $reset->getEmail()]);

        if (!$user) {
            return $this->json(['message' => 'Utilisateur introuvable'], 400);
        }

        $user->setPassword(
            $hasher->hashPassword($user, $password)
        );

        $em->remove($reset);
        $em->flush();

        return $this->json(['message' => 'Mot de passe modifié']);
    }


}