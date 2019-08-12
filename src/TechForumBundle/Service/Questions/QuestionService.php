<?php


namespace TechForumBundle\Service\Questions;


use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Question;
use TechForumBundle\Repository\QuestionRepository;
use TechForumBundle\Service\Users\UserService;

class QuestionService implements QuestionServiceInterface
{
    const TITLE_MIN_LENGTH = 6;
    const TITLE_MAX_LENGTH = 255;

    const DESCRIPTION_MIN_LENGTH = 6;

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

        $questions = $this->questionRepository->findQuestionsByCurrentUser($currentUser);

        return $questions;
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validateLength(FormInterface $form): bool
    {
        if (strlen($form['title']->getData()) < self::TITLE_MIN_LENGTH ||
        strlen($form['title']->getData()) > self::TITLE_MAX_LENGTH) {
            throw new \Exception("The title must be " . self::TITLE_MIN_LENGTH . " and " . self::TITLE_MAX_LENGTH . " symbols long!");
        } else if (strlen($form['description']->getData()) < 6) {
            throw new \Exception("The answer must be at least " . self::DESCRIPTION_MIN_LENGTH . " symbols long!");
        }

        return true;
    }

    public function getAllBySearch(string $search): array
    {
        $questions = $this->getAll();
        $resultQuestions = $this->searchAlgorithm($questions, $search);

        return $resultQuestions;
    }

    public function searchAlgorithm(array $questions, string $search): array
    {
        $questionsByIndex = [];
        $resultQuestions = [];
        foreach ($questions as $question) {
            $questionsByIndex[$question->getTitle()] = 0;
        }

        $search = explode(" ", $search);

        foreach ($search as $searchedWord) {
            $searchedWord = metaphone($searchedWord);

            foreach ($questionsByIndex as $question => $value) {
                $sound = " ";
                $words = explode(' ', $question);

                foreach ($words as $word) {
                    $sound .= metaphone($word) . " ";
                }

                if (strpos($sound, $searchedWord)) {
                    $questionsByIndex[$question]++;
                }
            }
        }
        arsort($questionsByIndex);
        foreach ($questionsByIndex as $question => $points) {
            if ($points > 0)
            {
                foreach ($questions as $similarQuestion) {
                    if ($similarQuestion->getTitle() === $question) {
                        $resultQuestions[] = $similarQuestion;
                    }
                }
            }
        }

        return $resultQuestions;
    }
}