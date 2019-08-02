<?php


namespace TechForumBundle\Service\Roles;


interface RoleServiceInterface
{
    public function findOneBy(string $criteria);
}