<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;
use TechForumBundle\Form\AnswerType;
use TechForumBundle\Repository\AnswerRepository;
use TechForumBundle\Service\Answers\AnswerService;
use TechForumBundle\Service\Questions\QuestionService;
use TechForumBundle\Service\Users\UserService;

class AnswerController extends Controller
{
    private $answerService;
    private $questionService;
    private $userService;

    public function __construct(AnswerService $answerService,
                                QuestionService $questionService,
                                UserService $userService)
    {
        $this->answerService = $answerService;
        $this->questionService = $questionService;
        $this->userService = $userService;
    }

    /**
     * @Route("/question/view/{id}", name="answer_question", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param Request $request
     * @param int $id
     *
     * @return Response
     */
    public function create(Request $request, int $id)
    {
        $answer = new Answer();

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        $this->answerService->create($answer, $id);

        return $this->redirectToRoute('question_view',
            [
                'id' => $id
            ]);
    }

    /**
     * @Route("/questions/switch_answer_like/{id}", name ="switch_answer_like")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function switchAnswerLike(int $id)
    {
        $answer = $this->answerService->answerById($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if ($currentUser->isAuthorOnAnswer($answer)) {
            return $this->redirectToRoute('forum_index');
        }

        $this->answerService->switchLikes($answer);
        $this->answerService->update($answer);

        return $this->redirectToRoute('question_view',
            ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * @Route("/switch_verfication/{id}", name = "switch_verification")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function switchVerification(int $id)
    {
        $currUser = $this->userService->currentUser();

        if (!$currUser->isAdmin()) {
            return $this->redirectToRoute('forum_index');
        }

        $answer = $this->answerService->answerById($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        $this->answerService->switchVerification($answer);
        $this->answerService->update($answer);

        return $this->redirectToRoute('question_view',
            ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * @param int $id
     *
     * @Route("/edit/answer/{id}", name="edit_answer", methods={"GET"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return RedirectResponse|Response
     */
    public function edit(int $id)
    {
        $answer = $this->answerService->answerById($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if (!$currentUser->isAuthorOnAnswer($answer)
            && (!$currentUser->isAdmin())) {
            return $this->redirectToRoute('forum_index');
        }

        return $this->render('answer/edit.html.twig',
            [
                'answer' => $answer,
                'form' => $this->createForm(AnswerType::class)->createView()
            ]);
    }

    /**
     * @param int $id
     * @param Request $request
     *
     * @Route("/edit/answer/{id}", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return RedirectResponse|Response
     */
    public function editProcess(int $id, Request $request)
    {
        $answer = $this->answerService->answerById($id);

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isValid()) {
            $this->answerService->update($answer);

            return $this->redirectToRoute('question_view',
                [
                    'id' => $answer->getQuestion()->getId()
                ]);
        }

        return $this->render('answer/edit.html.twig',
            [
                'answer' => $answer,
                'form' => $form->createView()
            ]);
    }

    /**
     *
     * @Route("answer/delete/{id}", name = "answer_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteAnswer(int $id)
    {

        $answer = $this->answerService->answerById($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if (!$currentUser->isAuthorOnAnswer($answer) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("forum_index");
        }

        $this->answerService->delete($answer);

        return $this->redirectToRoute('question_view',
            ['id' => $answer->getQuestion()->getId()]);
    }
}
