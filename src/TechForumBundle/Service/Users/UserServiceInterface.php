<?php


namespace TechForumBundle\Service\Users;

use Symfony\Component\Form\FormInterface;
use TechForumBundle\Entity\User;

interface UserServiceInterface
{
    public function save(User $user) : bool;
    public function currentUser(): ?User;
    public function userById(int $id): ?User;
    public function edit(User $user): bool;
    public function getAll(): array;
    public function rateUsers(): array;
    public function findOneByEmail(string $email): ?User;
    public function findOneByUsername(string $username): ?User;
    public function isUniqueRegister(FormInterface $form): bool;
    public function validateLength(FormInterface $form): bool;
    public function validatePasswords(FormInterface $form): bool;
    public function isUniqueEdit(FormInterface $form, array $data): bool;
}