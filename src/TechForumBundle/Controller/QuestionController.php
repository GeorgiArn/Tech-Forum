<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class QuestionController extends Controller
{
    /**
     * @param Request $request
     *
     * @Route("/question/ask", name="question_ask")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function create(Request $request)
    {
        $categories = $this->getDoctrine()
            ->getRepository('TechForumBundle:Category')
            ->getAllCategories();

        return $this->render('question/ask.html.twig',
            ['categories' => $categories]);
    }
}
