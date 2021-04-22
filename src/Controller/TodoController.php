<?php

namespace App\Controller;

use App\Entity\Todo;
use App\Handler\TodoHandler;
use App\Repository\TodoRepository;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


/**
 * Class TodoController
 * @package App\Controller
 * @Route("/api", name="todos", methods={"GET"})
 */
class TodoController extends BaseController
{
    /**
     * @Route("/todo", name="get_todos", methods={"GET"})
     *
     * @param TodoRepository $todoRepository
     * @return Response
     */
    public function index(TodoRepository $todoRepository): Response
    {
        $data = $todoRepository->findAll();
        return $this->json($data, Response::HTTP_OK);
    }

    /**
     * @Route("/todo", name="get_single_todo", methods={"GET"})
     *
     * @param Todo $todo
     * @return JsonResponse
     */
    public function details(Todo $todo)
    {
        return $this->json($todo->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/todo", name="create_todo", methods={"POST"})
     *
     * @param Request $request
     * @param TodoHandler $handler
     * @return JsonResponse
     */
    public function create(Request $request, TodoHandler $handler)
    {
        $todo = $this->getDataFromRequest($request);
        $data = $handler($todo, $this->getUser());

        if ($data instanceof Todo) {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $this->json($data->toArray(), Response::HTTP_CREATED);
    }

    /**
     * @Route("/todo/{todoId}", name="edit_todo", methods={"PUT"})
     *
     * @param string $todoId
     * @param Request $request
     * @param TodoHandler $handler
     * @return JsonResponse
     */
    public function edit(string $todoId, Request $request, TodoHandler $handler)
    {
        $todo = $this->getDataFromRequest($request);
        $data = $handler($todo, $this->getUser(), $todoId);

        if ($data instanceof Todo) {
            $this->entityManager->persist($data);
            $this->entityManager->flush();
        }

        return $this->json($data->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/todo/{todo}", name="change_priority_for_todo", methods={"PATCH"})
     *
     * @param Todo $todo
     * @param Request $request
     * @return JsonResponse
     */
    public function changePriority(Todo $todo, Request $request)
    {
        $data = $this->getDataFromRequest($request);
        if ($data) {
            $todo->setPriority($data['priority'] ?? $todo->getPriority());
            $this->entityManager->persist($todo);
            $this->entityManager->flush();
        }

        return $this->json($todo->toArray(), Response::HTTP_OK);
    }

    /**
     * @Route("/todo/{todo}", name="delete_todo", methods={"DELETE"})
     *
     * @param Todo $todo
     * @param Request $request
     * @return JsonResponse
     */
    public function delete(Todo $todo)
    {
        if ($todo instanceof Todo) {
            $this->entityManager->remove($todo);
        }

        return $this->json([
            'message' => 'record was deleted'
        ], Response::HTTP_NO_CONTENT);
    }
}
