<?php

namespace Demo\Model;

use Ramsey\Uuid\Uuid;

class Task
{
    private string $id;
    private string $title;
    private string $description;
    private bool $completed;
    private string $createdAt;
    private ?string $dueDate;
    private ?string $priority;

    public function __construct(
        string $title,
        string $description = '',
        ?string $dueDate = null,
        ?string $priority = 'medium',
        ?string $id = null
    ) {
        $this->id = $id ?? Uuid::uuid4()->toString();
        $this->title = $title;
        $this->description = $description;
        $this->completed = false;
        $this->createdAt = date('Y-m-d H:i:s');
        $this->dueDate = $dueDate;
        $this->priority = $priority;
    }

    public function getId(): string
    {
        return $this->id;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function isCompleted(): bool
    {
        return $this->completed;
    }

    public function setCompleted(bool $completed): self
    {
        $this->completed = $completed;
        return $this;
    }

    public function getCreatedAt(): string
    {
        return $this->createdAt;
    }

    public function getDueDate(): ?string
    {
        return $this->dueDate;
    }

    public function setDueDate(?string $dueDate): self
    {
        $this->dueDate = $dueDate;
        return $this;
    }

    public function getPriority(): ?string
    {
        return $this->priority;
    }

    public function setPriority(?string $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'completed' => $this->completed,
            'createdAt' => $this->createdAt,
            'dueDate' => $this->dueDate,
            'priority' => $this->priority,
        ];
    }

    public static function fromArray(array $data): self
    {
        $task = new self(
            $data['title'],
            $data['description'] ?? '',
            $data['dueDate'] ?? null,
            $data['priority'] ?? 'medium',
            $data['id'] ?? null
        );
        
        $task->completed = $data['completed'] ?? false;
        
        if (isset($data['createdAt'])) {
            $task->createdAt = $data['createdAt'];
        }
        
        return $task;
    }
}
