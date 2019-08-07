<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\User;
use TechForumBundle\Form\UserType;
use TechForumBundle\Repository\UserRepository;
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
        if ($form->isValid()) {
            $this->userService->save($user);
            return $this->redirectToRoute('security_login');
        }
        return $this->redirectToRoute("user_register");
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

    /**
     * @Route("/user/edit", name = "user_edit", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function edit()
    {
        $currUser = $this->getUser();

        return $this->render("users/edit.html.twig",
            [
                'user' => $currUser,
                'form' => $this->createForm(UserType::class)->createView()
            ]);
    }

    /**
     * @Route("/user/edit", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editProcess(Request $request)
    {
        /** @var User $currUser */
        $currUser = $this->getUser();
        $form = $this->createForm(UserType::class, $currUser);
        $form->remove('password');
        $form->handleRequest($request);

        $em = $this->getDoctrine()->getManager();
        $em->merge($currUser);
        $em->flush();

        return $this->redirectToRoute('user_profile',
            ['id' => $currUser->getId()]);
    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function leaderboard()
    {
        $users = $this
            ->getDoctrine()
            ->getRepository("TechForumBundle:User")
            ->findAll();

        return $this->render('users/leaderboard.html.twig',
            ['users' => $users]);
    }
}
