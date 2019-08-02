<?php


namespace TechForumBundle\Service\Users;

use Symfony\Component\Security\Core\Security;
use TechForumBundle\Entity\Role;
use TechForumBundle\Entity\User;
use TechForumBundle\Repository\UserRepository;
use TechForumBundle\Service\Encryption\BCryptService;
use TechForumBundle\Service\Encryption\EncryptionServiceInterface;
use TechForumBundle\Service\Roles\RoleService;

class UserService implements UserServiceInterface
{
    private $security;
    private $userRepository;
    private $encryptionService;
    private $roleService;

    public function __construct(Security $security,
                                UserRepository $userRepository,
                                BCryptService $encryptionService,
                                RoleService $roleService)
    {
        $this->security = $security;
        $this->userRepository = $userRepository;
        $this->encryptionService = $encryptionService;
        $this->roleService = $roleService;
    }

    /**
     * @param User $user
     * @return bool
     */
    public function save(User $user): bool
    {
        $passwordHash =
            $this->encryptionService->hash($user->getPassword());
        $user->setPassword($passwordHash);

        /** @var Role $roleUser */
        $roleUser = $this->roleService->findOneBy("ROLE_USER");
        $user->addRole($roleUser);

        return $this->userRepository->insert($user);
    }

    /**
     * @return User|null|object
     */
    public function currentUser(): ?User
    {
        return $this->security->getUser();
    }
}