<?php


namespace TechForumBundle\Service\Users;

use TechForumBundle\Entity\User;

interface UserServiceInterface
{
    public function save(User $user) : bool;
    public function currentUser(): ?User;
    public function userById(int $id): ?User;
    public function edit(User $user): bool;
    public function getAll(): array;
    public function rateUsers(): array;
}