<?php


namespace TechForumBundle\Service\Users;

use TechForumBundle\Entity\User;

interface UserServiceInterface
{
    public function save(User $user) : bool;
    public function currentUser(): ?User;
}