<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Bundle\FrameworkBundle\Tests\Fixtures\Validation\Article;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;
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
    public function createQuestion(Request $request)
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


        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        return $this->render('question/question.html.twig', ['question' => $question]);
    }

    /**
     * @Route("question/edit/{id}", name = "question_edit")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param $id
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editQuestion($id, Request $request)
    {
        /** @var Question $question */
        $question = $this->getDoctrine()
            ->getRepository(Question::class)
            ->find($id);

        $categories = $this->getDoctrine()
            ->getRepository('TechForumBundle:Category')
            ->getAllCategories();

        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser->isAuthor($question) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("forum_index");
        }

        $form = $this->createForm(QuestionType::class, $question);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($question);
            $em->flush();

            return $this->redirectToRoute('question_view',
                ['id' => $question->getId()]);
        }

        return $this->render('question/edit.html.twig',
            [
                'question' => $question,
                'form' => $form->createView(),
                'categories' => $categories,
            ]);
    }

    /**
     *
     * @Route("question/delete/{id}", name = "question_delete")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     * 
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteQuestion($id)
    {

        $question = $this->getDoctrine()
            ->getRepository("TechForumBundle:Question")
            ->find($id);

        if ($question === null) {
            return $this->redirectToRoute('forum_index');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if (!$currentUser->isAuthor($question) && !$currentUser->isAdmin()) {
            return $this->redirectToRoute("forum_index");
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($question);
        $em->flush();

        return $this->redirectToRoute('forum_index');
    }

    /**
     * @Route("/questions/my_questions", name="my_questions")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function getAllQuestionsByUser()
    {
        $questions = $this
            ->getDoctrine()
            ->getRepository(Question::class)
            ->findBy(['author' => $this->getUser()]);


        return $this->render("question/my_questions.html.twig",
            [
                'questions' => $questions,
            ]);
    }


    /**
     * @Route("/questions/switch_like/{id}", name ="switch_like")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function switchLike($id)
    {
        $question = $this->getDoctrine()
            ->getRepository("TechForumBundle:Question")
            ->find($id);

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->isAuthor($question)) {
            return $this->redirectToRoute('forum_index');
        }

        if ($question->isLikedBy($currentUser)) {
            $question->removeLike($currentUser);
        } else {
            $question->addLike($currentUser);
        }

        $em = $this->getDoctrine()->getManager();
        $em->merge($question);
        $em->flush();

        return $this->redirectToRoute('question_view', ['id' => $question->getId()]);
    }
}
