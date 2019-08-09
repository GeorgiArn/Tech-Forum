<?php


namespace TechForumBundle\Service\Answers;


use TechForumBundle\Entity\Answer;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;

interface AnswerServiceInterface
{
    public function answerById(int $id): ?Answer;
    public function getAll(): array;
    public function create(Answer $answer, int $questionId): bool;
    public function update(Answer $answer): bool;
    public function delete(Answer $answer): bool;
    public function getAnswersByQuestion(Question $question): array;
    public function deleteAnswersByQuestion(Question $question): bool;
    public function isLikedByCurrentUser(Answer $answer): bool;
    public function addLike(Answer $answer): bool;
    public function removeLike(Answer $answer): bool;
    public function switchLikes(Answer $answer): bool;
    public function switchVerification(Answer $answer): bool;
    public function getAnswersByCurrentUser(): array;
}