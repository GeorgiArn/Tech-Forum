<?php

namespace TechForumBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;
use TechForumBundle\Form\AnswerType;
use TechForumBundle\Repository\AnswerRepository;

class AnswerController extends Controller
{

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/question/view/{id}", name="answer_question", methods={"POST"})
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function createAnswer(Request $request, $id)
    {
        /** @var Question $question */
        $question = $this->getDoctrine()->getRepository(Question::class)
            ->find($id);

        $answer = new Answer();
        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);
        $answer->setAuthor($this->getUser());
        $answer->setQuestion($question);
        $em = $this->getDoctrine()->getManager();
        $em->persist($answer);
        $em->flush();

        return $this->redirectToRoute('question_view',
            [
                'id' => $id
            ]);
    }

    /**
     * @Route("/questions/switch_answer_like/{id}", name ="switch_answer_like")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function switchAnswerLike($id)
    {
        $answer = $this->getDoctrine()
            ->getRepository("TechForumBundle:Answer")
            ->find($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        /** @var User $currentUser */
        $currentUser = $this->getUser();

        if ($currentUser->isAuthorOnAnswer($answer)) {
            return $this->redirectToRoute('forum_index');
        }

        if ($answer->isLikedBy($currentUser)) {
            $answer->removeLike($currentUser);
        } else {
            $answer->addLike($currentUser);
        }

        $em = $this->getDoctrine()->getManager();
        $em->merge($answer);
        $em->flush();

        return $this->redirectToRoute('question_view', ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * @Route("/switch_verfication/{id}", name = "switch_verification")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @param int $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function switchVerification(int $id)
    {
        /** @var User $currUser */
        $currUser = $this->getUser();

        if (!$currUser->isAdmin()) {
            return $this->redirectToRoute('forum_index');
        }

        $answer = $this->getDoctrine()
            ->getRepository("TechForumBundle:Answer")
            ->find($id);

        if ($answer === null) {
            return $this->redirectToRoute('forum_index');
        }

        if ($answer->isVerified()) {
            $answer->setIsVerified(false);
        } else {
            $answer->setIsVerified(true);
        }

        $em = $this->getDoctrine()->getManager();
        $em->persist($answer);
        $em->flush();

        return $this->redirectToRoute('question_view', ['id' => $answer->getQuestion()->getId()]);
    }

    /**
     * @param Request $request
     * @param $id
     *
     * @Route("/edit/answer/{id}", name="edit_answer")
     * @Security("is_granted('IS_AUTHENTICATED_FULLY')")
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAnswer(Request $request, int $id)
    {
        $answer = $this->getDoctrine()
            ->getRepository("TechForumBundle:Answer")
            ->find($id);

        if (!$this->getUser()->isAuthorOnAnswer($answer)
        && (!$this->getUser()->isAdmin())) {
            return $this->redirectToRoute('forum_index');
        }

        $form = $this->createForm(AnswerType::class, $answer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->merge($answer);
            $em->flush();

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
}
