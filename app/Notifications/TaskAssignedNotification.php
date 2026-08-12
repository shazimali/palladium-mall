<?php

namespace App\Notifications;

use App\Models\Task;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;

class TaskAssignedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Task $task,
        public User $assigner
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'        => 'task_assigned',
            'task_id'     => $this->task->id,
            'title'       => $this->task->title,
            'status'      => $this->task->status,
            'priority'    => $this->task->priority,
            'due_at'      => $this->task->due_at ? $this->task->due_at->toIso8601String() : null,
            'assigner_id' => $this->assigner->id,
            'assigner_name' => $this->assigner->name,
            'message'     => "{$this->assigner->name} assigned a new task to you: '{$this->task->title}'",
            'url'         => route('tasks.index', ['task_id' => $this->task->id]),
        ];
    }

    public function toBroadcast(object $notifiable): BroadcastMessage
    {
        return new BroadcastMessage($this->toArray($notifiable));
    }
}
