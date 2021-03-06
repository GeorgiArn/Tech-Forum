<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;
use TechForumBundle\Form\AnswerType;
use TechForumBundle\Form\QuestionType;
use TechForumBundle\Service\Answers\AnswerService;
use TechForumBundle\Service\Categories\CategoryService;
use TechForumBundle\Service\Questions\QuestionService;
use TechForumBundle\Service\Search\SearchService;
use TechForumBundle\Service\Users\UserService;

class QuestionController extends Controller
{
    /**
     * @var QuestionService
     */
    private $questionService;

    /**
     * @var CategoryService
     */
    private $categoryService;

    /**
     * @var AnswerService
     */
    private $answerService;

    /**
     * @var UserService
     */
    private $userService;

    /**
     * @var SearchService
     */
    private $searchService;

    public function __construct(QuestionService $questionService,
                                CategoryService $categoryService,
                                AnswerService $answerService,
                                UserService $userService,
                                SearchService $searchService)
    {
        $this->questionService = $questionService;
        $this->categoryService = $categoryService;
        $this->answerService = $answerService;
        $this->userService = $userService;
        $this->searchService = $searchService;
    }

    /**
     * @Route("/question/ask", name="question_ask", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @return Response
     */
    public function create()
    {
        $categories = $this->categoryService->getAll();

        return $this->render('question/ask.html.twig',
            [
                'form' => $this->createForm(QuestionType::class)->createView(),
                'categories' => $categories
            ]
        );
    }

    /**
     * @Route("/question/ask", methods={"POST"})
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function createProcess(Request $request)
    {
        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        try {
            $this->questionService->validateLength($form);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('question_ask');
        }

        $this->questionService->create($question);
        $this->addFlash("infos", "Successfully asked question!");

        return $this->redirectToRoute('forum_index');
    }

    /**
     * @Route("/question/view/{id}", name = "question_view", methods={"GET"})
     *
     * @param int $id
     * @return Response
     */
    public function view(int $id)
    {
        $question = $this->questionService->questionById($id);

        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        $answers = $this->answerService->getAnswersByQuestion($question);

        return $this->render('question/question.html.twig',
            [
                'question' => $question,
                'answers' => $answers,
                'form' => $this->createForm(AnswerType::class)->createView()
            ]);
    }

    /**
     * @Route("question/edit/{id}", name = "question_edit", methods={"GET"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * @param int $id
     * @return RedirectResponse|Response
     */
    public function edit(int $id) {

        $question = $this->questionService->questionById($id);

        if ($question === null) {
            return $this->redirectToRoute("forum_index");
        }

        $categories = $this->categoryService->getAll();

        $currentUser = $this->userService->currentUser();

        if (!$currentUser->isAuthorOnQuestion($question) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("forum_index");
        }

        return $this->render('question/edit.html.twig',
            [
                'form' => $this->createForm(QuestionType::class)->createView(),
                'question' => $question,
                'categories' => $categories,
            ]);
    }

    /**
     * @Route("question/edit/{id}", methods={"POST"})
     *
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @param Request $request
     * @return RedirectResponse
     */
    public function editProcess(int $id, Request $request)
    {
        $question = $this->questionService->questionById($id);

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        try {
            $this->questionService->validateLength($form);
        } catch (\Exception $ex) {
            $this->addFlash("errors", $ex->getMessage());
            return $this->redirectToRoute('question_edit', ['id' => $id]);
        }

        $this->questionService->update($question);
        $this->addFlash("infos", "Successfully edited question!");

        return $this->redirectToRoute('forum_index');
    }

    /**
     *
     * @Route("question/delete/{id}", name = "question_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * 
     * @param int $id
     * @return RedirectResponse
     */
    public function deleteQuestion(int $id)
    {
        $question = $this->questionService->questionById($id);

        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if (!$currentUser->isAuthorOnQuestion($question) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("forum_index");
        }

        $this->answerService->deleteAnswersByQuestion($question);
        $this->questionService->delete($question);
        $this->addFlash("infos", "Successfully deleted question!");

        return $this->redirectToRoute('forum_index');
    }

    /**
     * @Route("/questions/switch_question_like/{id}", name ="switch_question_like")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param $id
     * @return RedirectResponse
     */
    public function switchQuestionLike($id)
    {
        $question = $this->questionService->questionById($id);

        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        $currentUser = $this->userService->currentUser();

        if ($currentUser->isAuthorOnQuestion($question)) {
            return $this->redirectToRoute('forum_index');
        }

        $this->questionService->switchLikes($question);
        $this->questionService->update($question);

        return $this->redirectToRoute('question_view', ['id' => $question->getId()]);
    }

    /**
     * @Route("/questions/my_questions", name="my_questions")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return Response
     */
    public function myQuestions()
    {
        $questions = $this->questionService
            ->getQuestionsByCurrentUser();

        return $this->render("question/my_questions.html.twig",
            [
                'questions' => $questions,
            ]);
    }

    /**
     * @Route("/questions/search", name="search_questions")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param string $search
     * @return Response
     */
    public function search()
    {
        $search = $_POST['search'];

        $questions = $this->searchService->getAllBySearch($search);

        return $this->render('question/searched_questions.html.twig',
            ['questions' => $questions]);
    }

}
