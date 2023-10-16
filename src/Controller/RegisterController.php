<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegisterType;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegisterController extends AbstractController
{
    #[Route('/inscription', name: 'register')]
    public function index(UserRepository $userRepository, Request $request, UserPasswordHasherInterface $hasher, TokenStorageInterface $tokenStorage): Response
    {
        $notification = null;

        $user = new User();

        $form = $this->createForm(RegisterType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isSubmitted()){
            $searchEmail = $userRepository->findOneByEmail($user->getEmail());

            if(!$searchEmail) {
                $password = $hasher->hashPassword($user, $user->getPassword());
                $user->setPassword($password);


                $userRepository->save($user, true);

                $token = new UsernamePasswordToken($user, $password, ['ROLE_USER'], 'main', $user->getRoles());
                $tokenStorage->setToken($token);

                $notification = "Votre inscription c'est correctemt déroulée.";
            } else {
                $notification = "L'email que vous avez renseigné existe déjà.";
            }

            return $this->redirectToRoute('app_login');
        }

        return $this->render('register/index.html.twig',[
            'form' => $form->createView(),
            'notification' => $notification
        ]);
    }
}
