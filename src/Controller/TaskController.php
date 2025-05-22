<?php

namespace App\Controller;

use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api/tasks')]
#[IsGranted('ROLE_USER')]
class TaskController extends AbstractController
{
    private EntityManagerInterface $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    #[Route('', name: 'task_index', methods: ['GET'])]
    public function index(Request $request, UserInterface $user): JsonResponse
    {
        $status = $request->query->get('status');
        $page = max(1, (int) $request->query->get('page', 1));
        $limit = max(1, (int) $request->query->get('limit', 10));
        $offset = ($page - 1) * $limit;

        $repo = $this->em->getRepository(Task::class);
        $qb = $repo->createQueryBuilder('t');

        if ($status) {
            $qb->andWhere('t.status = :status')->setParameter('status', $status);
        }

        $tasks = $qb->setFirstResult($offset)->setMaxResults($limit)->getQuery()->getResult();

        $data = array_map(fn($task) => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'created_at' => $task->getCreatedAt(),
            'updated_at' => $task->getUpdatedAt()
        ], $tasks);

        return $this->json([
            'page' => $page,
            'limit' => $limit,
            'count' => count($data),
            'tasks' => $data,
        ]);
    }

    #[Route('', name: 'task_create', methods: ['POST'])]
    public function create(Request $request, UserInterface $user, ValidatorInterface $validator): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['title'])) {
            return $this->json(['error' => 'Title is required'], 400);
        }

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? '');
        $task->setStatus($data['status'] ?? 'new');

        $errors = $validator->validate($task);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->em->persist($task);
        $this->em->flush();

        return $this->json(['message' => 'Task created', 'id' => $task->getId()], 201);
    }

    #[Route('/{id}', name: 'task_show', methods: ['GET'])]
    public function show(int $id, UserInterface $user): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        return $this->json([
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
            'created_at' => $task->getCreatedAt(),
            'updated_at' => $task->getUpdatedAt()
        ]);
    }

    #[Route('/{id}', name: 'task_update', methods: ['PUT'])]
    public function update(Request $request, int $id, UserInterface $user, ValidatorInterface $validator): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }

        $errors = $validator->validate($task);

        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }

            return $this->json(['errors' => $errorMessages], 400);
        }

        $this->em->flush();

        return $this->json(['message' => 'Task updated']);
    }

    #[Route('/{id}', name: 'task_delete', methods: ['DELETE'])]
    public function delete(int $id, UserInterface $user): JsonResponse
    {
        $task = $this->em->getRepository(Task::class)->find($id);

        if (!$task) {
            return $this->json(['error' => 'Task not found'], 404);
        }

        $this->em->remove($task);
        $this->em->flush();

        return $this->json(['message' => 'Task deleted']);
    }
}