<?php

namespace TechForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Fixtures\Validation\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Question;
use TechForumBundle\Service\Questions\QuestionService;

class HomeController extends Controller
{
    /**
     * @var QuestionService
     */
    private $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    /**
     * @Route("/", name="forum_index")
     * @return Response
     */
    public function indexAction()
    {
        $questions = $this->questionService->getAll();

        return $this->render('default/index.html.twig',
            [
                'questions' => $questions,
            ]);
    }
}
