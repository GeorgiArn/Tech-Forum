<?php


namespace TechForumBundle\Service\Questions;


use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\Question;
use TechForumBundle\Entity\User;

interface QuestionServiceInterface
{
    public function questionById(int $id): ?Question;
    public function create(Question $question): bool;
    public function update(Question $question): bool;
    public function delete(Question $question): bool;
    public function isLikedByCurrentUser(Question $question): bool;
    public function addLike(Question $question): bool;
    public function removeLike(Question $question): bool;
    public function getAll(): array;
    public function switchLikes(Question $question): bool;
    public function getQuestionsByCurrentUser(): array;
    public function validateLength(FormInterface $form): bool;
}