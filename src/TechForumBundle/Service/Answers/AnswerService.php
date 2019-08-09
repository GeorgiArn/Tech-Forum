<?php


namespace TechForumBundle\Service\Answers;


use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
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
        return $this->answerRepository->findBy(
            ['question' => $question]
        );
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
        if ($this->isLikedByCurrentUser($answer)) {
            $this->removeLike($answer);
        } else {
            $this->addLike($answer);
        }
        return true;
    }

    public function switchVerification(Answer $answer): bool
    {
        if ($answer->isVerified()) {
            $answer->setIsVerified(false);
        } else {
            $answer->setIsVerified(true);
        }

        return true;
    }
}