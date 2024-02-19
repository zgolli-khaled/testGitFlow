<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\String\ByteString;

#[Route('/api/user', name: 'app_user_')]
class UserController extends AbstractController
{
    #[Route('/add', name: 'add')]
    public function add(Request $request,SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): JsonResponse
    {
        /**
         * @var User $user
         */
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $user->setPassword($userPasswordHasher->hashPassword($user, $user->getPassword()));
        $em->persist($user);
        $em->flush();
        $jsonBook = $serializer->serialize($user, 'json');

        return new JsonResponse($jsonBook, Response::HTTP_CREATED, [], true);
    }

    #[IsGranted('ROLE_ADMIN', message: 'Vous n\'avez pas les droits suffisants pour ajouter  un user')]
    #[Route('/addUser' ,name: 'addUser', methods: ['POST'])]
    public function sendEmail(MailerInterface $mailer,Request $request,SerializerInterface $serializer, EntityManagerInterface $em, UserPasswordHasherInterface $userPasswordHasher): Response
    {
        /**
         * @var User $user
         */
       
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');
        $randomPassword=ByteString::fromRandom(32)->toString();
        $user->setPassword($userPasswordHasher->hashPassword($user, $randomPassword));
        $user->setRoles(["ROLE_USER"]);
        $em->persist($user);
        $em->flush();
        $jsonBook = $serializer->serialize($user, 'json');

       
        $email = (new Email())
            ->from('khaled.zgolli0@gmail.com')
            ->to('khaled.zgolli0@gmail.com')
            //->cc('cc@example.com')
            //->bcc('bcc@example.com')
            //->replyTo('fabien@example.com')
            //->priority(Email::PRIORITY_HIGH)
            ->subject('User created')
            ->text('Sending emails is fun again!')
            ->html('<p>welcome  </br> mail : ' . $user->getEmail() . '</br> password: '.$randomPassword .'</p>');

        $mailer->send($email);
        return new JsonResponse($jsonBook, Response::HTTP_CREATED, [], true);
    }

}
