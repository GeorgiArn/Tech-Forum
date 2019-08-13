<?php


namespace TechForumBundle\Service\Search;


interface SearchServiceInterface
{
    public function getAllBySearch(string $search): array;
    public function searchAlgorithm(array $questions, string $search): array;
    public function setSearchIndexToZero(array $questions): array;
    public function transformWordToMetaphoneSound(array $words): string;
    public function fulfillResultQuestions(array $questions, array $questionsByIndex): array;
    public function searchSoundAlgorithm(string $searchedWord,
                                         array $sounds,
                                         array $questionsByIndex,
                                         string $question): array;
    public function getAllMatchedSounds(array $search, array $questionsByIndex);
}