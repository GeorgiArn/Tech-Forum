<?php


namespace TechForumBundle\Service\Search;


use TechForumBundle\Service\Questions\QuestionService;

class SearchService implements SearchServiceInterface
{
    private $questionService;

    public function __construct(QuestionService $questionService)
    {
        $this->questionService = $questionService;
    }

    public function getAllBySearch(string $search): array
    {
        $questions = $this->questionService->getAll();
        $resultQuestions = $this->searchAlgorithm($questions, $search);

        return $resultQuestions;
    }

    public function searchAlgorithm(array $questions, string $search): array
    {
        $questionsByIndex = $this->setSearchIndexToZero($questions);
        $search = explode(" ", $search);

        $questionsByIndex = $this->getAllMatchedSounds($search, $questionsByIndex);
        arsort($questionsByIndex);

        $resultQuestions = $this->fulfillResultQuestions($questions, $questionsByIndex);

        return $resultQuestions;
    }

    public function setSearchIndexToZero(array $questions): array
    {
        $questionsByIndex = [];
        foreach ($questions as $question) {
            $questionsByIndex[$question->getTitle()] = 0;
        }
        return $questionsByIndex;
    }

    public function transformWordToMetaphoneSound(array $words): string
    {
        $sound = "";
        foreach ($words as $word) {
            $sound .= metaphone($word) . " ";
        }
        return $sound;
    }

    public function fulfillResultQuestions(array $questions, array $questionsByIndex): array {
        $resultQuestions = [];

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

    public function searchSoundAlgorithm(string $searchedWord,
                                         array $sounds,
                                         array $questionsByIndex,
                                         string $question): array
    {
        foreach ($sounds as $word) {
            if ($searchedWord === $word) {
                $questionsByIndex[$question]++;
            }
        }

        return $questionsByIndex;
    }

    public function getAllMatchedSounds(array $search, array $questionsByIndex)
    {
        foreach ($search as $searchedWord) {
            $searchedWord = metaphone($searchedWord);
            foreach ($questionsByIndex as $question => $value) {
                $sound = $this->transformWordToMetaphoneSound(explode(' ', $question));
                $sounds = explode(' ', $sound);
                $questionsByIndex = $this->searchSoundAlgorithm($searchedWord, $sounds, $questionsByIndex, $question);
            }
        }
        return $questionsByIndex;
    }
}