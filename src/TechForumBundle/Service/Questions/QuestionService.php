<?php


namespace TechForumBundle\Service\Questions;


use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Question;
use TechForumBundle\Repository\QuestionRepository;
use TechForumBundle\Service\Users\UserService;

class QuestionService implements QuestionServiceInterface
{

    private $questionRepository;
    private $userService;

    public function __construct(QuestionRepository $questionRepository,
                                UserService $userService)
    {
        $this->questionRepository = $questionRepository;
        $this->userService = $userService;
    }

    /**
     * @param int $id
     * @return Question|null|object
     */
    public function questionById(int $id): ?Question
    {
        return $this->questionRepository->find($id);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function create(Question $question): bool
    {
        $author = $this->userService->currentUser();

        $question->setAuthor($author);

        return $this->questionRepository->insert($question);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function update(Question $question): bool
    {
        return $this->questionRepository->merge($question);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function delete(Question $question): bool
    {
        return $this->questionRepository->remove($question);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function isLikedByCurrentUser(Question $question): bool
    {
        $currentUser = $this->userService->currentUser();

        foreach ($question->getLikers() as $liker) {
            if ($currentUser->getId () === $liker->getId()) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function addLike(Question $question): bool
    {
        $currentUser = $this->userService->currentUser();

        $question->getLikers()[] = $currentUser;

        return true;
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function removeLike(Question $question): bool
    {
        $currentUser = $this->userService->currentUser();

        $question->getLikers()->removeElement($currentUser);

        return true;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->questionRepository->findAll();
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function switchLikes(Question $question): bool
    {
        $author = $question->getAuthor();

        if ($this->isLikedByCurrentUser($question)) {
            $this->removeLike($question);
            $points = $author->getTotalPoints() - 2;
            $author->setTotalPoints($points);
        } else {
            $this->addLike($question);
            $points = $author->getTotalPoints() + 2;
            $author->setTotalPoints($points);
        }
        return true;
    }

    public function getQuestionsByCurrentUser(): array
    {
        $currentUser = $this->userService->currentUser();

        $questions = $this->questionRepository->findBy([
            'author' => $currentUser
        ]);

        return $questions;
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validateLength(FormInterface $form): bool
    {
        if (strlen($form['title']->getData()) < 2 ||
        strlen($form['title']->getData()) > 150) {
            throw new \Exception("The title must be 4 and 150 symbols long!");
        } else if (strlen($form['description']->getData()) < 6) {
            throw new \Exception("The answer must be at least 6 symbols long!");
        }

        return true;
    }
}