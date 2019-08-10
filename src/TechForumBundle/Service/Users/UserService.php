<?php


namespace TechForumBundle\Service\Users;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\Security\Core\Security;
use TechForumBundle\Entity\Role;
use TechForumBundle\Entity\User;
use TechForumBundle\Repository\UserRepository;
use TechForumBundle\Service\Encryption\BCryptService;
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

    /**
     * @param int $id
     * @return User|null|object
     */
    public function userById(int $id): ?User
    {
        return $this->userRepository->find($id);
    }

    /**
     * @param User $user
     * @return bool
     */
    public function edit(User $user): bool
    {
        return $this->userRepository->merge($user);
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->userRepository->findAll();
    }

    /**
     * @return array
     */
    public function rateUsers(): array
    {
        return $this->userRepository->sort();
    }

    /**
     * @param string $email
     * @return User|null|object
     */
    public function findOneByEmail(string $email): ?User
    {
        return $this->userRepository->findOneBy(['email' => $email]);
    }

    /**
     * @param string $username
     * @return User|null|object
     */
    public function findOneByUsername(string $username): ?User
    {
        return $this->userRepository->findOneBy(['username' => $username]);
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function isUniqueRegister(FormInterface $form): bool
    {

        if (null !== $this
                ->findOneByEmail($form['email']->getData())) {
            throw new \Exception("Email already taken!");
        } else if (null !== $this
                ->findOneByUsername($form['username']->getData())) {
            throw new \Exception("Username already taken!");
        }

        return true;
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validateLength(FormInterface $form): bool
    {
        if (strlen($form['email']->getData()) < 4
            || strlen(strlen($form['email']->getData()) > 50)) {
            throw new \Exception("Email must be between 4 and 50 symbols!");
        } else if (strlen($form['fullName']->getData()) < 3
                   || strlen($form['fullName']->getData()) > 50) {
            throw new \Exception("Full Name must be between 3 and 50 symbols!");
        } else if (strlen($form['username']->getData()) < 2
                   || strlen($form['username']->getData()) > 50) {
            throw new \Exception("Username must be between 2 and 50 symbols!");
        } else if (isset($form['password']['first'])) {
            if (strlen($form['password']['first']->getData()) < 6
                 || strlen($form['password']['first']->getData()) > 50) {
                throw new \Exception("Password must be between 6 and 50 symbols!");
            }
        }

        return true;
    }

    /**
     * @param FormInterface $form
     * @return bool
     * @throws \Exception
     */
    public function validatePasswords(FormInterface $form): bool
    {
        if ($form['password']['first']->getData() !==
        $form['password']['second']->getData()) {
            throw new \Exception("Passwords mismatch");
        }

        return true;
    }

    /**
     * @param FormInterface $form
     * @param array $data
     * @return bool
     * @throws \Exception
     */
    public function isUniqueEdit(FormInterface $form, array $data): bool
    {
        if (null !== $this
                ->findOneByEmail($form['email']->getData()) &&
                    $form['email']->getData() !== $data[0]) {
                throw new \Exception("Email already taken!");
        } else if (null !== $this
                ->findOneByUsername($form['username']->getData()) &&
                   $form['username']->getData() !== $data[1]) {
            throw new \Exception("Username already taken!");
        }

        return true;
    }
}