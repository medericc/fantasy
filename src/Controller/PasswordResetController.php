<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Csrf\TokenGenerator\TokenGeneratorInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
class PasswordResetController extends AbstractController
{
    #[Route('/forgot-password', name: 'app_forgot_password')]
    public function request(Request $request, UserRepository $userRepository, TokenGeneratorInterface $tokenGenerator, EntityManagerInterface $entityManager, MailerInterface $mailer): Response
    {
        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');

            // Rechercher l'utilisateur par email
            $user = $userRepository->findOneBy(['email' => $email]);

            if ($user) {
                // Générer un token de réinitialisation de mot de passe
                $token = $tokenGenerator->generateToken();

                // Sauvegarder le token dans l'entité User
                $user->setResetToken($token);
                $entityManager->persist($user);
                $entityManager->flush();

                // Créer l'URL de réinitialisation de mot de passe
                $url = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

                // Envoyer l'email de réinitialisation
                $emailMessage = (new Email())
                    ->from('no-reply@yourdomain.com')
                    ->to($user->getEmail())
                    ->subject('Password Reset Request')
                    ->html('<p>To reset your password, please click the following link: <a href="' . $url . '">Reset Password</a></p>');

                $mailer->send($emailMessage);

                $this->addFlash('success', 'An email has been sent to reset your password.');
            } else {
                $this->addFlash('error', 'Email address not found.');
            }
        }

        return $this->render('security/forgot_password.html.twig');
    }
    
    #[Route('/reset-password/{token}', name: 'app_reset_password')]
    public function reset(Request $request, string $token, UserRepository $userRepository, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
    {
        // Trouver l'utilisateur par le token
        $user = $userRepository->findOneBy(['resetToken' => $token]);

        if (!$user) {
            $this->addFlash('error', 'Token invalid');
            return $this->redirectToRoute('app_login');
        }

        if ($request->isMethod('POST')) {
            $newPassword = $request->request->get('password');
            
            // Hacher et mettre à jour le mot de passe
            $user->setPassword($passwordHasher->hashPassword($user, $newPassword));
            $user->setResetToken(null); // Effacer le token après l'utilisation
            $entityManager->flush();

            $this->addFlash('success', 'Password successfully updated.');

            return $this->redirectToRoute('app_login');
        }

        return $this->render('security/reset_password.html.twig', ['token' => $token]);
    }
}
