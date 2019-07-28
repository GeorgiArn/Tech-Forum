<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Fixtures\Validation\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Question;
use TechForumBundle\Form\QuestionType;

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

        $question = new Question();

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $question->setAuthor($this->getUser());
            $em = $this->getDoctrine()->getManager();
            $em->persist($question);
            $em->flush();

            return $this->redirectToRoute('forum_index');
        }

        $categories = $this->getDoctrine()
            ->getRepository('TechForumBundle:Category')
            ->getAllCategories();

        return $this->render('question/ask.html.twig',
            [
                'form' => $form->createView(),
                'categories' => $categories,
            ]);
    }

    /**
     * @Route("/question/{id}", name = "question_view")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewQuestion($id)
    {
        $question = $this->getDoctrine()
            ->getRepository('TechForumBundle:Question')
            ->find($id);

        return $this->render('question/question.html.twig', ['question' => $question]);
    }
}
