<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
     * @return Response
     */
    public function register()
    {
        return $this->render('users/register.html.twig',
            [
                'form' => $this->createForm(UserType::class)
                    ->createView()
            ]
        );
    }

    /**
     * @Route("/register", methods={"POST"})
     * @param Request $request
     * @return Response
     */
    public function registerProcess(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        try {
            $this->userService->validateLength($form);
            $this->userService->validatePasswords($form);
            $this->userService->isUniqueRegister($form);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('user_register');
        }

        $this->userService->save($user);

        $this->addFlash("infos", "You have been registered
        successfully! Now login to our platform to continue.");

        return $this->redirectToRoute('security_login');
    }

    /**
     * @Route("/profile/{id}", name = "user_profile", methods={"GET"})
     * @param int $id
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function profile(int $id)
    {
        $user = $this->userService->userById($id);

        return $this->render("users/profile.html.twig",
            ['user' => $user]);
    }

    /**
     * @Route("/user/edit", name = "user_edit", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return RedirectResponse|Response
     */
    public function edit()
    {
        $currUser = $this->userService->currentUser();

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
     * @return RedirectResponse|Response
     */
    public function editProcess(Request $request)
    {
        $data = [];
        $currUser = $this->userService->currentUser();
        $data[] = $currUser->getEmail();
        $data[] = $currUser->getUsername();

        $form = $this->createForm(UserType::class, $currUser);
        $form->remove('password');
        $form->handleRequest($request);

        try {
            $this->userService->validateLength($form);
            $this->userService->isUniqueEdit($form, $data);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('user_edit');
        }

        $this->userService->edit($currUser);
        $this->addFlash("infos", "Successfully edited profile!");

        return $this->redirectToRoute('user_profile',
            ['id' => $currUser->getId()]);

    }

    /**
     * @Route("/leaderboard", name="leaderboard")
     *
     * @return RedirectResponse|Response
     */
    public function leaderboard()
    {
        $users = $this->userService->rateUsers();

        return $this->render('users/leaderboard.html.twig',
            ['users' => $users]);
    }
}
