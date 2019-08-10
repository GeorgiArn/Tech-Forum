<?php


namespace TechForumBundle\Service\Answers;


use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;
use TechForumBundle\Repository\AnswerRepository;
use TechForumBundle\Repository\QuestionRepository;
use TechForumBundle\Repository\UserRepository;
use TechForumBundle\Service\Questions\QuestionService;
use TechForumBundle\Service\Users\UserService;

class AnswerService implements AnswerServiceInterface
{
    private $answerRepository;
    private $questionService;
    private $userService;

    public function __construct(AnswerRepository $answerRepository,
                                QuestionService $questionService,
                                UserService $userService)
    {
        $this->answerRepository = $answerRepository;
        $this->questionService = $questionService;
        $this->userService = $userService;
    }

    /**
     * @param int $id
     * @return Answer|null|object
     */
    public function answerById(int $id): ?Answer
    {
        return $this->answerRepository->find($id);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->answerRepository->findAll();
    }

    /**
     * @param Answer $answer
     * @param int $questionId
     * @return bool
     */
    public function create(Answer $answer, int $questionId): bool
    {
        $currentUser = $this->userService->currentUser();
        $question = $this->questionService->questionById($questionId);

        $answer->setAuthor($currentUser);
        $answer->setQuestion($question);

        return $this->answerRepository->insert($answer);
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function update(Answer $answer): bool
    {
        return $this->answerRepository->merge($answer);
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function delete(Answer $answer): bool
    {
        return $this->answerRepository->remove($answer);
    }

    /**
     * @param Question $question
     * @return array
     */
    public function getAnswersByQuestion(Question $question): array
    {
        return $this->answerRepository->findAnswersByQuestion($question);
    }

    /**
     * @param Question $question
     * @return bool
     */
    public function deleteAnswersByQuestion(Question $question): bool
    {
        $answers = $this->getAnswersByQuestion($question);

        foreach ($answers as $answer) {
            $this->delete($answer);
        }

        return true;
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function isLikedByCurrentUser(Answer $answer): bool
    {
        $currentUser = $this->userService->currentUser();

        foreach ($answer->getLikers() as $liker) {
            if ( $currentUser->getId() === $liker->getId() ) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function addLike(Answer $answer): bool
    {
        $currentUser = $this->userService->currentUser();

        $answer->getLikers()[] = $currentUser;

        return true;
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function removeLike(Answer $answer): bool
    {
        $currentUser = $this->userService->currentUser();

        $answer->getLikers()->removeElement($currentUser);

        return true;
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function switchLikes(Answer $answer): bool
    {
        $author = $answer->getAuthor();

        if ($this->isLikedByCurrentUser($answer)) {
            $this->removeLike($answer);
            $points = $author->getTotalPoints() - 15;
            $author->setTotalPoints($points);
        } else {
            $this->addLike($answer);
            $points = $author->getTotalPoints() + 15;
            $author->setTotalPoints($points);
        }
        return true;
    }

    /**
     * @param Answer $answer
     * @return bool
     */
    public function switchVerification(Answer $answer): bool
    {
        $author = $answer->getAuthor();

        if ($answer->isVerified()) {
            $answer->setIsVerified(false);
            $points = $author->getTotalPoints() - 100;
            $author->setTotalPoints($points);
        } else {
            $answer->setIsVerified(true);
            $points = $author->getTotalPoints() + 100;
            $author->setTotalPoints($points);
        }

        return true;
    }

    /**
     * @return array
     */
    public function getAnswersByCurrentUser(): array
    {
        $currentUser = $this->userService->currentUser();

        $answers = $this->answerRepository->findAnswersByCurrentUser($currentUser);

        return $answers;
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validateLength(FormInterface $form): bool
    {
        if (strlen($form['content']->getData()) < 2) {
            throw new \Exception("The answer must be at least 2 symbols long!");
        }

        return true;
    }
}