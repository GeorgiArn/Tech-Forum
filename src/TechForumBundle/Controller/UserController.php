<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\User;
use TechForumBundle\Form\UserType;
use TechForumBundle\Service\Users\UserServiceInterface;

class UserController extends Controller
{
    /**
     * @var UserServiceInterface
     */
    private $userService;

    public function __construct(UserServiceInterface $userService)
    {
        $this->userService = $userService;
    }

    /**
     * @Route("/register", name="user_register", methods={"GET"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(Request $request)
    {
        return $this->render('users/register.html.twig',
            [
                'form' => $this->createForm(UserType::class)->createView()
            ]
        );
    }

    /**
     * @Route("/register", methods={"POST"})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function registerProcess(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);
        $this->userService->save($user);
        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/profile/{id}", name = "user_profile", methods={"GET"})
     * @param int $id
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function profile(int $id)
    {
        $user = $this
            ->getDoctrine()
            ->getRepository("TechForumBundle:User")
            ->find($id);

        return $this->render("users/profile.html.twig",
            ['user' => $user]);
    }
}
