<?php

namespace Demo\Controller;

use Demo\Model\Task;
use Demo\Service\TaskService;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;

class TaskController
{
    private TaskService $taskService;
    private Environment $twig;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;

        $loader = new FilesystemLoader(__DIR__ . '/../../templates');
        $this->twig = new Environment($loader, [
            'cache' => false,
            'debug' => true,
        ]);
        $this->twig->addGlobal('app_name', 'Task Manager');
    }

    public function index(): string
    {
        $tasks = $this->taskService->getAllTasks();

        return $this->twig->render('index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    public function create(array $data): string
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $dueDate = !empty($data['due_date']) ? $data['due_date'] : null;
            $priority = $data['priority'] ?? 'medium';

            if (!empty($title)) {
                try {
                    $task = new Task($title, $description, $dueDate, $priority);
                    $this->taskService->createTask($task);

                    if (!headers_sent()) {
                        header('Location: index.php');
                        exit;
                    }
                    echo '<script>window.location.href = "index.php";</script>';
                    exit;
                } catch (\Exception $e) {
                    error_log('Error creating task: ' . $e->getMessage());
                }
            }
        }

        return $this->twig->render('create.html.twig');
    }

    public function update(array $data, string $id): string
    {
        $task = $this->taskService->getTaskById($id);

        if (!$task) {
            return $this->notFound();
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $title = $data['title'] ?? '';
            $description = $data['description'] ?? '';
            $dueDate = !empty($data['due_date']) ? $data['due_date'] : null;
            $priority = $data['priority'] ?? 'medium';
            $completed = isset($data['completed']);

            if (!empty($title)) {
                try {
                    $task->setTitle($title)
                        ->setDescription($description)
                        ->setDueDate($dueDate)
                        ->setPriority($priority)
                        ->setCompleted($completed);

                    $this->taskService->updateTask($task);

                    if (!headers_sent()) {
                        header('Location: index.php');
                        exit;
                    }
                    echo '<script>window.location.href = "index.php";</script>';
                    exit;
                } catch (\Exception $e) {
                    error_log('Error updating task: ' . $e->getMessage());
                }
            }
        }

        return $this->twig->render('update.html.twig', [
            'task' => $task,
        ]);
    }

    public function delete(string $id): void
    {
        try {
            $this->taskService->deleteTask($id);
        } catch (\Exception $e) {
            error_log('Error deleting task: ' . $e->getMessage());
        }

        if (!headers_sent()) {
            header('Location: index.php');
            exit;
        }
        echo '<script>window.location.href = "index.php";</script>';
        exit;
    }

    public function toggleComplete(string $id): void
    {
        try {
            $task = $this->taskService->getTaskById($id);

            if ($task) {
                $task->setCompleted(!$task->isCompleted());
                $this->taskService->updateTask($task);
            }
        } catch (\Exception $e) {
            error_log('Error toggling task completion: ' . $e->getMessage());
        }

        if (!headers_sent()) {
            header('Location: index.php');
            exit;
        }
        echo '<script>window.location.href = "index.php";</script>';
        exit;
    }

    private function notFound(): string
    {
        http_response_code(404);
        return $this->twig->render('404.html.twig');
    }
}
