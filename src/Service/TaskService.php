<?php

namespace Demo\Service;

use Demo\Model\Task;
use Monolog\Logger;
use Monolog\Level;
use Monolog\Handler\StreamHandler;
use Predis\Client;

class TaskService
{
    private RedisService $redis;
    private Logger $logger;
    private string $taskPrefix = 'task:';
    private string $taskListKey = 'tasks';

    public function __construct(RedisService $redis = null)
    {
        $this->redis = $redis ?? new RedisService();
        $this->logger = new Logger('task-service');
        $this->logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
    }

    public function getAllTasks(): array
    {
        $tasks = [];
        $taskIds = $this->redis->get($this->taskListKey);

        if (!$taskIds) {
            return [];
        }

        $taskIds = json_decode((string)$taskIds, true) ?? [];

        foreach ($taskIds as $id) {
            try {
                $taskData = $this->redis->jsonGet($this->taskPrefix . $id, '$');
                if ($taskData) {
                    if (isset($taskData['title'])) {
                        $tasks[] = Task::fromArray($taskData);
                    } else {
                        $this->logger->warning('Task data missing title', ['id' => $id, 'data' => $taskData]);
                    }
                }
            } catch (\Exception $e) {
                $this->logger->error('Error getting task', ['id' => $id, 'error' => $e->getMessage()]);
            }
        }

        return $tasks;
    }

    public function getTaskById(string $id): ?Task
    {
        try {
            $taskData = $this->redis->jsonGet($this->taskPrefix . $id, '$');

            if (!$taskData) {
                return null;
            }

            if (!isset($taskData['title'])) {
                $this->logger->warning('Task data missing title', ['id' => $id, 'data' => $taskData]);
                return null;
            }

            return Task::fromArray($taskData);
        } catch (\Exception $e) {
            $this->logger->error('Error getting task by ID', ['id' => $id, 'error' => $e->getMessage()]);
            return null;
        }
    }

    public function createTask(Task $task): Task
    {
        $this->redis->jsonSet($this->taskPrefix . $task->getId(), $task->toArray(), '$');

        $taskIds = $this->redis->get($this->taskListKey);
        $taskIds = $taskIds ? json_decode((string)$taskIds, true) : [];
        $taskIds[] = $task->getId();
        $this->redis->set($this->taskListKey, json_encode($taskIds));

        $this->logger->info('Task created', ['id' => $task->getId()]);

        return $task;
    }

    public function updateTask(Task $task): bool
    {
        $key = $this->taskPrefix . $task->getId();

        if (!$this->redis->exists($key)) {
            $this->logger->warning('Attempt to update a non-existent task', ['id' => $task->getId()]);
            return false;
        }

        $this->redis->jsonSet($key, $task->toArray(), '$');
        $this->logger->info('Task updated', ['id' => $task->getId()]);

        return true;
    }

    public function deleteTask(string $id): bool
    {
        $key = $this->taskPrefix . $id;

        if (!$this->redis->exists($key)) {
            $this->logger->warning('Attempt to delete a non-existent task', ['id' => $id]);
            return false;
        }

        $this->redis->jsonDel($key, '$');

        $taskIds = $this->redis->get($this->taskListKey);
        $taskIds = $taskIds ? json_decode((string)$taskIds, true) : [];
        $taskIds = array_filter($taskIds, function ($taskId) use ($id) {
            return $taskId !== $id;
        });
        $this->redis->set($this->taskListKey, json_encode(array_values($taskIds)));

        $this->logger->info('Task deleted', ['id' => $id]);

        return true;
    }
}
