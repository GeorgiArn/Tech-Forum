<?php

namespace TechForumBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Fixtures\Validation\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Question;

class HomeController extends Controller
{
    /**
     * @Route("/", name="forum_index")
     * @return Response
     */
    public function indexAction()
    {
        $questions = $this->getDoctrine()
            ->getRepository(Question::class)->findAll();

        return $this->render('default/index.html.twig',
            [
                'questions' => $questions,
            ]);
    }
}
