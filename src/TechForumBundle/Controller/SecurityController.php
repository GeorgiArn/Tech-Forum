<?php

namespace TechForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use TechForumBundle\Service\Users\UserService;

class SecurityController extends Controller
{
    private $userService;

    public function __construct(UserService $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/login", name="security_login")
     * @param AuthenticationUtils $authenticationUtils
     * @return Response
     */
    public function login(AuthenticationUtils $authenticationUtils)
    {
        $error = $authenticationUtils->getLastAuthenticationError();

        if ($error !== null) {
            $this->addFlash("errors", "Invalid Credentials");
        }
        return $this->render('security/login.html.twig');
    }

    /**
     * @Route("/logout", name="security_logout")
     * @throws \Exception
     */
    public function logout()
    {
        throw new \Exception("Logout Failed!");
    }
}
