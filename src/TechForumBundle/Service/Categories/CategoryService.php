<?php


namespace TechForumBundle\Service\Categories;


use TechForumBundle\Repository\CategoryRepository;

class CategoryService implements CategoryServiceInterface
{
    private $categoryRepository;

    public function __construct(CategoryRepository $categoryRepository)
    {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->categoryRepository->findAll();
    }
}