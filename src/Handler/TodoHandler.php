<?php

namespace App\Handler;

use App\Entity\Todo;
use App\Entity\User;
use App\Repository\TodoRepository;

/**
 * Class TodoCreateHandler
 * @package App\Handler
 */
class TodoHandler
{
    /**
     * @var TodoRepository
     */
    private $repository;

    /**
     * TodoHandler constructor.
     * @param TodoRepository $repository
     */
    public function __construct(TodoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * @param array $todo
     * @param User $user
     * @param string|null $todoId
     * @return Todo|null
     */
    public function __invoke(array $todo, User $user, ?string $todoId = null): ?Todo
    {
        if (!$todo) {
            return null;
        }

        if ($todoId) {
            $todoObject = $this->repository->findOneBy(['id' => $todoId]);
        } else {
            $todoObject = new Todo();
        }

        $todoObject
            ->setTitle($todo['title'])
            ->setDescription($todo['description'] ?? '')
            ->setDueDate(new \DateTime())
            ->setCreatedAt()
            ->setUser($user)
            ->setPriority($todo['priority'] ?? '');


        return $todoObject;
    }
}
